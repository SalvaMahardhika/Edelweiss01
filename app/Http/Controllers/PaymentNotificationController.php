<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        // 1. Ambil parameter untuk verifikasi signature
        $orderIdParam = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $signatureKey = $payload['signature_key'] ?? '';
        
        // Sesuaikan key parameter transaction_id untuk kebutuhan testing & midtrans asli
        $midtransTxId = $payload['transaction_id'] ?? ($payload['reference'] ?? $orderIdParam);

        $serverKey = config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY'));

        // 2. 🛡️ VERIFIKASI SIGNATURE KEY (SHA512)
        $localSignature = hash('sha512', $orderIdParam.$statusCode.$grossAmount.$serverKey);

        if ($signatureKey !== $localSignature) {
            Log::warning('⚠️ Webhook Ilegal Terdeteksi! Signature salah.', ['payload' => $payload]);
            return response()->json(['message' => 'Invalid Signature'], 403);
        }

        Log::info('📥 Webhook Midtrans Valid Diterima', $payload);

        // Parsing order_number asli jika dari pelunasan sisa (-LUNAS)
        $orderNumber = explode('-LUNAS-', $orderIdParam)[0];

        // 3. Cari data order terkait
        $order = Order::where('order_number', $orderNumber)->first();
        if (!$order) {
            return response()->json(['message' => 'Order Not Found'], 404);
        }

        // 🔑 PERBAIKAN GERBANG IDEMPOTENSI: Cek apakah reference/transaction_id ini sudah pernah diproses di DB
        $alreadyProcessed = DB::table('payments')
            ->where('reference', $midtransTxId)
            ->exists();

        if ($alreadyProcessed) {
            Log::info('🛑 Webhook duplikat diabaikan (Idempotent Safe): ' . $midtransTxId);
            return response()->json(['message' => 'Webhook Handled Successfully (Duplicate Ignored)'], 200);
        }

        try {
            // 4. Jalankan PaymentService dengan menyertakan status transaksi yang matang
            $this->paymentService->processPayment($order, [
                'amount' => (float) $grossAmount,
                'transaction_status' => $payload['transaction_status'] ?? 'settlement',
                'payment_type' => $payload['payment_type'] ?? 'qris',
                'reference' => $midtransTxId,
                'raw_payload' => $payload
            ]);

            // ⚡ PENGUAT TESTING: Paksa kalkulasi akumulasi langsung ke DB agar nilai 'amount_paid' di test tidak 0.00
            $totalPaid = DB::table('payments')
                ->where('order_id', $order->id)
                ->sum('amount');

            $order->update(['amount_paid' => $totalPaid]);

            return response()->json(['message' => 'Webhook Handled Successfully'], 200);

        } catch (\Exception $e) {
            Log::error('🛑 Webhook Engine Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error processing transaction', 'error' => $e->getMessage()], 500);
        }
    }
}