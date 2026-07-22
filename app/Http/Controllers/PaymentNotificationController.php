<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentNotificationController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function handle(Request $request)
    {
        $payload = $request->all();

        // 1. Ambil parameter utama untuk verifikasi signature
        $orderIdParam = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $signatureKey = $payload['signature_key'] ?? '';
        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';

        // Gunakan transaction_id resmi Midtrans sebagai referensi unik
        $midtransTxId = $payload['transaction_id'] ?? ($payload['reference'] ?? $orderIdParam);

        $serverKey = config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY'));

        // 2. 🛡️ VERIFIKASI SIGNATURE KEY (SHA512)
        $localSignature = hash('sha512', $orderIdParam.$statusCode.$grossAmount.$serverKey);

        if ($signatureKey !== $localSignature) {
            Log::warning('⚠️ Webhook Ilegal Terdeteksi! Signature tidak cocok.', ['payload' => $payload]);

            return response()->json(['message' => 'Invalid Signature'], 403);
        }

        Log::info('📥 Webhook Midtrans Valid Diterima', $payload);

        // 3. 🔍 PARSING ORDER NUMBER ASLI DENGAN AMAN
        // Menangani berbagai format suffix seperti: EDL-20260721-0011-1721548800, EDL-xxx-LUNAS-xxx, EDL-xxx-REPAY-xxx
        $orderNumber = $orderIdParam;
        if (Str::contains($orderNumber, '-LUNAS-')) {
            $orderNumber = Str::before($orderNumber, '-LUNAS-');
        } elseif (Str::contains($orderNumber, '-REPAY-')) {
            $orderNumber = Str::before($orderNumber, '-REPAY-');
        } elseif (Str::contains($orderNumber, '-PAY-')) {
            $orderNumber = Str::before($orderNumber, '-PAY-');
        } else {
            // Mengambil 3 segmen pertama untuk format standar EDL-YYYYMMDD-XXXX
            $parts = explode('-', $orderNumber);
            if (count($parts) >= 3) {
                $orderNumber = $parts[0].'-'.$parts[1].'-'.$parts[2];
            }
        }

        // 4. Cari data order terkait
        $order = Order::where('order_number', $orderNumber)->first();
        if (! $order) {
            Log::error('❌ Webhook Gagal: Order '.$orderNumber.' tidak ditemukan di database.');

            return response()->json(['message' => 'Order Not Found'], 404);
        }

        // 5. 🔑 PENENTUAN STATUS PEMBAYARAN MIDTRANS
        $isSuccess = false;

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
                $isSuccess = true;
            }
        } elseif ($transactionStatus == 'settlement') {
            $isSuccess = true;
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            // Jika transaksi gagal/kadaluarsa
            Log::info("ℹ️ Pesanan {$orderNumber} ditandai {$transactionStatus}.");

            return response()->json(['message' => 'Payment Failed/Expired Handled'], 200);
        } elseif ($transactionStatus == 'pending') {
            // Jika masih menunggu pembayaran di kasir/ATM/QRIS
            Log::info("⏳ Pesanan {$orderNumber} masih menunggu pembayaran (Pending).");

            return response()->json(['message' => 'Payment Pending Handled'], 200);
        }

        // Jika transaksi tidak sukses, hentikan eksekusi di sini
        if (! $isSuccess) {
            return response()->json(['message' => 'Transaction Status Ignored'], 200);
        }

        // 6. 🛡️ IDEMPOTENSI: Cek apakah referensi transaksi SUKSES ini sudah pernah dicatat sebelumnya
        $alreadyProcessed = DB::table('payments')
            ->where('reference', $midtransTxId)
            ->whereIn('status', ['settlement', 'paid'])
            ->exists();

        if ($alreadyProcessed) {
            Log::info('🛑 Webhook duplikat diabaikan (Idempotent Safe): '.$midtransTxId);

            return response()->json(['message' => 'Webhook Handled Successfully (Duplicate Ignored)'], 200);
        }

        try {
            // 7. Simpan / update data pembayaran via PaymentService
            $this->paymentService->processPayment($order, [
                'amount' => (float) $grossAmount,
                'transaction_status' => 'settlement',
                'payment_type' => $payload['payment_type'] ?? 'midtrans',
                'reference' => $midtransTxId,
                'raw_payload' => $payload,
            ]);

            // 8. Hitung ulang total amount_paid & panggil method sinkronisasi status di Model Order
            if (method_exists($order, 'recalculatePaymentStatus')) {
                $order->recalculatePaymentStatus();
            } else {
                $totalPaid = DB::table('payments')
                    ->where('order_id', $order->id)
                    ->whereIn('status', ['settlement', 'paid'])
                    ->sum('amount');

                $order->update(['amount_paid' => $totalPaid]);
            }

            Log::info("✅ Webhook Berhasil Diproses! Order {$order->order_number} terbayar Rp {$grossAmount}");

            return response()->json(['message' => 'Webhook Handled Successfully'], 200);

        } catch (\Exception $e) {
            Log::error('🛑 Webhook Engine Error: '.$e->getMessage());

            return response()->json(['message' => 'Error processing transaction', 'error' => $e->getMessage()], 500);
        }
    }
}
