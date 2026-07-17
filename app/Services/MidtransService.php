<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

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
        // 1. Tentukan nominal gross_amount berdasarkan payment_plan atau jenis pembayaran
        $grossAmount = $order->total_amount;

        if ($paymentType === 'initial' && $order->payment_plan === 'dp') {
            $grossAmount = $order->dp_amount;
        } elseif ($paymentType === 'settlement' || $paymentType === 'repayment') {
            $grossAmount = method_exists($order, 'remaining') ? $order->remaining() : ($order->total_amount - $order->amount_paid);
        }

        $grossAmountRounded = intval(round($grossAmount));

        $orderIdParam = ($paymentType === 'settlement' || $paymentType === 'repayment')
            ? $order->order_number.'-LUNAS-'.time()
            : $order->order_number;

        // 2. Susun payload standar Midtrans API
        $params = [
            'transaction_details' => [
                'order_id' => $orderIdParam,
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

        // 3. Tentukan Endpoint API Midtrans (Sandbox vs Production)
        $baseUrl = $this->isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        // 4. Kirim Request dengan pemetaan DNS manual via cURL option (Mencegah DNS Resolving Timeout)
        $response = Http::withoutVerifying()
            ->connectTimeout(10)
            ->timeout(30)
            ->withOptions([
                'curl' => [
                    // 🆕 Memaksa cURL langsung tahu IP sandbox Midtrans tanpa nanya DNS lokal lagi
                    CURLOPT_RESOLVE => [
                        'app.sandbox.midtrans.com:443:103.127.16.5',
                    ],
                ],
            ])
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->withBasicAuth($this->serverKey, '')
            ->post($baseUrl, $params);

        if ($response->failed()) {
            throw new \Exception('Midtrans API Error: '.$response->body());
        }

        // 5. Kembalikan token string yang didapat dari response body
        return $response->json()['token'];
    }
}
