<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    protected $serverKey;

    protected $isProduction;

    public function __construct()
    {
        // Ambil konfigurasi dari .env
        $this->serverKey = config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY'));
        $this->isProduction = (bool) config('services.midtrans.is_production', env('MIDTRANS_IS_PRODUCTION', false));
    }

    /**
     * Dapatkan Snap Token untuk transaksi Order.
     */
    public function getSnapToken(Order $order, string $paymentType = 'initial'): string
    {
        // Handling safe string value jika payment_plan berbentuk BackedEnum
        $paymentPlanVal = is_object($order->payment_plan) ? $order->payment_plan->value : $order->payment_plan;

        // 1. Tentukan nominal gross_amount berdasarkan payment_plan atau jenis pembayaran
        $grossAmount = $order->total_amount;

        if ($paymentType === 'initial' && $paymentPlanVal === 'dp') {
            $grossAmount = $order->dp_amount;
        } elseif ($paymentType === 'settlement' || $paymentType === 'repayment') {
            $grossAmount = method_exists($order, 'remaining') ? $order->remaining() : ($order->total_amount - $order->amount_paid);
        }

        $grossAmountRounded = intval(round((float) $grossAmount));

        // 2. BUAT ORDER ID UNIK UNTUK MIDTRANS
        // Untuk transaksi awal gunakan order_number asli, sedangkan pelunasan gunakan suffix -LUNAS-timestamp
        $midtransOrderId = ($paymentType === 'settlement' || $paymentType === 'repayment')
            ? $order->order_number.'-LUNAS-'.time()
            : $order->order_number;

        // 3. Susun payload standar Midtrans API
        $params = [
            'transaction_details' => [
                'order_id' => $midtransOrderId,
                'gross_amount' => $grossAmountRounded,
            ],
            'customer_details' => [
                'first_name' => $order->customer_name,
                'phone' => $order->customer_phone,
                'email' => $order->customer_email ?? 'customer@edelweiss.com',
            ],
            'expiry' => [
                'start' => now()->format('Y-m-d H:i:s O'),
                'duration' => 24,
                'unit' => 'hours',
            ],
        ];

        // 4. Tentukan Endpoint API Midtrans (Sandbox vs Production)
        $baseUrl = $this->isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        // 5. Kirim Request dengan Penanganan Error dan Timeout yang Aman
        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(5)            // Batas waktu inisiasi koneksi (5 detik)
                ->timeout(10)                  // Batas waktu total penantian response (10 detik)
                ->retry(2, 1000)               // Coba ulang maksimal 2x dengan jeda 1 detik jika bermasalah
                ->withOptions([
                    'http_errors' => false,    // Mencegah Laravel throw RequestException mentah saat status 400
                ])
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->withBasicAuth($this->serverKey, '')
                ->post($baseUrl, $params);

        } catch (ConnectionException $e) {
            // Tangkap error jika terjadi timeout / gagal koneksi ke server Midtrans
            Log::error('Midtrans Connection Timeout/Error: '.$e->getMessage());

            throw new \Exception('Gagal terhubung ke gateway pembayaran Midtrans (Timeout). Silakan periksa jaringan Anda.');
        }

        // Jika Midtrans merespon dengan status error (4xx / 5xx)
        if ($response->failed()) {
            $errorBody = $response->json();
            $errorMessage = $errorBody['error_messages'][0] ?? $response->body();

            Log::error('Midtrans API Error Response', ['body' => $errorBody]);

            throw new \Exception('Midtrans API Error: '.$errorMessage);
        }

        $responseData = $response->json();

        if (! isset($responseData['token'])) {
            throw new \Exception('Gagal mendapatkan Snap Token dari Midtrans.');
        }

        // 6. Kembalikan token string yang didapat dari response body
        return $responseData['token'];
    }
}
