<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentNotificationController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        // 1. Ambil parameter krusial untuk verifikasi signature
        $orderIdParam = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $signatureKey = $payload['signature_key'] ?? '';

        // 🔑 KOLOM BARU: Gunakan parameter transaction_id atau fallback ke order_id
        $midtransTxId = $payload['transaction_id'] ?? $orderIdParam;

        $serverKey = config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY'));

        // 2. 🛡️ VERIFIKASI SIGNATURE KEY (SHA512)
        $localSignature = hash('sha512', $orderIdParam.$statusCode.$grossAmount.$serverKey);

        if ($signatureKey !== $localSignature) {
            Log::warning('⚠️ Webhook Ilegal Terdeteksi! Signature salah.', ['payload' => $payload]);

            return response()->json(['message' => 'Invalid Signature'], 403);
        }

        // 3. Simpan Payload Mentah untuk Audit
        Log::info('📥 Webhook Midtrans Valid Diterima', $payload);

        // Parsing order_number asli jika transaksi ini berasal dari pelunasan sisa (-LUNAS)
        $orderNumber = explode('-LUNAS-', $orderIdParam)[0];

        // 4. Cari data order terkait
        $orderCheck = Order::where('order_number', $orderNumber)->first();
        if (! $orderCheck) {
            return response()->json(['message' => 'Order Not Found'], 404);
        }

        $transactionStatus = $payload['transaction_status'];
        $type = $payload['payment_type'] ?? 'qris';

        // 5. 🔄 PROSES IDEMPOTEN MUTLAK (Sesuai Struktur Kolom Migrasi Payments)
        DB::transaction(function () use ($orderNumber, $transactionStatus, $grossAmount, $midtransTxId, $type) {

            // 🔒 LOCK BARIS: Ambil data order segar langsung dari DB
            $order = Order::where('order_number', $orderNumber)->lockForUpdate()->first();

            if (! $order) {
                return;
            }

            // 🔑 CEK IDEMPOTENSI: Cocokkan ke kolom 'reference' sesuai struktur tabel migrasimu
            $alreadyProcessed = DB::table('payments')
                ->where('reference', $midtransTxId)
                ->exists();

            if ($alreadyProcessed) {
                Log::info('🛑 Webhook duplikat diabaikan (Idempotent Safe): '.$midtransTxId);

                return;
            }

            if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {

                // 🔑 SIMPAN DATA: Sesuaikan key array dengan kolom migrasi ('reference', 'method', 'status')
                DB::table('payments')->insert([
                    'order_id' => $order->id,
                    'type' => $order->amount_paid == 0 ? 'down_payment' : 'settlement',
                    'provider' => 'midtrans',
                    'method' => $type, // Memetakan ke kolom 'method' kamu
                    'amount' => (float) $grossAmount,
                    'status' => 'settlement',
                    'reference' => $midtransTxId, // Memetakan ke kolom 'reference' kamu
                    'payload' => json_encode($midtransTxId),
                    'paid_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Hitung akumulasi uang masuk asli dari tabel payments secara real-time
                $totalPaidFromDb = DB::table('payments')
                    ->where('order_id', $order->id)
                    ->sum('amount');

                $order->amount_paid = $totalPaidFromDb;

                // Pemetaan Status Order secara Otoritatif Berbasis Enum
                if ($order->amount_paid >= $order->total_amount) {
                    if (defined('\App\Enums\OrderStatus::PAID')) {
                        $order->status = OrderStatus::PAID;
                    } elseif (defined('\App\Enums\OrderStatus::SUCCESS')) {
                        $order->status = OrderStatus::SUCCESS;
                    } else {
                        $order->status = 'paid';
                    }
                } else {
                    if (defined('\App\Enums\OrderStatus::PARTIAL')) {
                        $order->status = OrderStatus::PARTIAL;
                    } elseif (defined('\App\Enums\OrderStatus::PROCESSING')) {
                        $order->status = OrderStatus::PROCESSING;
                    } elseif (defined('\App\Enums\OrderStatus::PENDING')) {
                        $order->status = OrderStatus::PENDING;
                    } else {
                        try {
                            $order->status = OrderStatus::from('PARTIAL');
                        } catch (\ValueError $e) {
                            $order->status = OrderStatus::from('pending');
                        }
                    }
                }

                $order->save();
            }
        });

        return response()->json(['message' => 'Webhook Handled Successfully'], 200);
    }
}
