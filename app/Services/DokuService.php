<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DokuService
{
    protected string $clientId;

    protected string $secretKey;

    protected bool $isProduction;

    public function __construct()
    {
        // Ambil konfigurasi dari config/services.php atau .env (Default: Sandbox/false)
        $this->clientId = (string) config('services.doku.client_id', env('DOKU_CLIENT_ID', ''));
        $this->secretKey = (string) config('services.doku.secret_key', env('DOKU_SECRET_KEY', ''));
        $this->isProduction = (bool) config('services.doku.is_production', env('DOKU_IS_PRODUCTION', false));
    }

    /**
     * Dapatkan Payment URL dari DOKU Checkout untuk transaksi Order.
     * Menggantikan fungsi getSnapToken milik Midtrans.
     */
    public function getPaymentUrl(Order $order, string $paymentType = 'initial'): string
    {
        // Validasi ketersediaan Client ID & Secret Key
        if (empty($this->clientId) || empty($this->secretKey)) {
            Log::error('DOKU Client ID atau Secret Key belum dikonfigurasi di file .env');
            throw new \Exception('Konfigurasi DOKU Payment Gateway belum diisi.');
        }

        // Handling safe string value jika payment_plan berbentuk BackedEnum / Enum / String
        $paymentPlanVal = is_object($order->payment_plan)
            ? ($order->payment_plan->value ?? $order->payment_plan->name)
            : $order->payment_plan;

        // 1. Tentukan nominal gross_amount berdasarkan payment_plan atau jenis pembayaran
        $grossAmount = $order->total_amount;

        if ($paymentType === 'initial' && strtolower((string) $paymentPlanVal) === 'dp') {
            $grossAmount = $order->dp_amount ?? ($order->total_amount / 2);
        } elseif ($paymentType === 'settlement' || $paymentType === 'repayment') {
            $grossAmount = method_exists($order, 'remaining') ? $order->remaining() : ($order->total_amount - $order->amount_paid);
        }

        $grossAmountRounded = intval(round((float) $grossAmount));

        // 2. BUAT INVOICE NUMBER UNIK UNTUK DOKU
        $invoiceNumber = ($paymentType === 'settlement' || $paymentType === 'repayment')
            ? $order->order_number.'-LUNAS-'.time()
            : $order->order_number;

        // 3. Susun Line Items (Barang) untuk DOKU
        $lineItems = [];
        if ($order->relationLoaded('items') && $order->items->count() > 0) {
            foreach ($order->items as $item) {
                $lineItems[] = [
                    'id' => (string) $item->product_id,
                    'name' => substr((string) $item->product_name, 0, 250),
                    'price' => intval(round((float) $item->unit_price)),
                    'quantity' => (int) $item->quantity,
                ];
            }

            // Jika pembayaran DP 50%, atur line_items tunggal agar total harga sesuai grossAmountRounded
            if ($paymentType === 'initial' && strtolower((string) $paymentPlanVal) === 'dp') {
                $lineItems = [[
                    'id' => $order->order_number.'-DP',
                    'name' => 'Uang Muka (DP 50%) - '.$order->order_number,
                    'price' => $grossAmountRounded,
                    'quantity' => 1,
                ]];
            }
        }

        // 4. Pisahkan Nama Depan dan Belakang Customer
        $nameParts = explode(' ', trim((string) $order->customer_name), 2);
        $firstName = $nameParts[0] ?? 'Customer';
        $lastName = $nameParts[1] ?? '';

        // 5. Susun Payload JSON sesuai Dokumentasi DOKU Checkout API
        $params = [
            'order' => [
                'amount' => $grossAmountRounded,
                'invoice_number' => $invoiceNumber,
                'currency' => 'IDR',
                'auto_redirect' => true,
                'callback_url' => route('checkout.success', ['order' => $order->order_number]),
            ],
            'payment' => [
                'payment_due_date' => 60, // Batas waktu pembayaran (60 menit)
            ],
            'additional_info' => [
                'override_notification_url' => url('/api/doku/webhook'),
            ],
            'customer' => [
                'id' => 'CUST-'.($order->user_id ?? $order->id),
                'name' => $firstName,
                'last_name' => $lastName,
                'phone' => (string) $order->customer_phone,
                'email' => $order->customer_email ?? 'customer@edelweiss.com',
                'address' => substr((string) ($order->delivery_address ?? 'Toko Edelweiss Bakery'), 0, 390),
            ],
        ];

        // Masukkan line_items jika tersedia
        if (! empty($lineItems)) {
            $params['order']['line_items'] = $lineItems;
        }

        // Masukkan alamat pengiriman jika order_type delivery
        if ($order->order_type === 'delivery' && ! empty($order->delivery_address)) {
            $params['shipping_address'] = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'address' => substr((string) $order->delivery_address, 0, 390),
                'phone' => (string) $order->customer_phone,
                'country_code' => 'IDN',
            ];
        }

        // 6. Target Path & Endpoint API DOKU (Otomatis Gunakan SANDBOX jika $isProduction = false)
        $targetPath = '/checkout/v1/payment';
        $baseUrl = $this->isProduction
            ? 'https://api.doku.com'.$targetPath
            : 'https://api-sandbox.doku.com'.$targetPath;

        // 7. GENERATE DOKU HMACSHA256 SIGNATURE HEADERS
        $requestId = (string) Str::uuid();
        $timestamp = gmdate('Y-m-d\TH:i:s\Z'); // Format UTC ISO8601

        $jsonBody = json_encode($params);

        // A. Hitung Digest (Base64 dari SHA256 Body JSON)
        $digest = base64_encode(hash('sha256', $jsonBody, true));

        // B. Susun String Header Komponen
        $signatureRaw = "Client-Id:{$this->clientId}\n".
                         "Request-Id:{$requestId}\n".
                         "Request-Timestamp:{$timestamp}\n".
                         "Request-Target:{$targetPath}\n".
                         "Digest:{$digest}";

        // C. Hitung Signature HMACSHA256
        $signature = base64_encode(hash_hmac('sha256', $signatureRaw, $this->secretKey, true));

        // 8. Kirim Request ke DOKU API
        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(10)
                ->timeout(20)
                ->retry(2, 1000)
                ->withOptions([
                    'http_errors' => false,
                ])
                ->withHeaders([
                    'Client-Id' => $this->clientId,
                    'Request-Id' => $requestId,
                    'Request-Timestamp' => $timestamp,
                    'Signature' => 'HMACSHA256='.$signature,
                    'Content-Type' => 'application/json',
                ])
                ->post($baseUrl, $params);

        } catch (ConnectionException $e) {
            Log::error('DOKU Connection Timeout/Error: '.$e->getMessage());
            throw new \Exception('Gagal terhubung ke gateway pembayaran DOKU (Timeout). Silakan periksa koneksi internet Anda.');
        }

        // Jika DOKU merespon dengan status error (4xx / 5xx)
        if ($response->failed()) {
            $errorBody = $response->json();
            $errorMessage = $errorBody['error']['message']
                ?? ($errorBody['error_messages'][0]
                ?? $response->body());

            Log::error('DOKU API Error Response', ['body' => $errorBody, 'status' => $response->status()]);
            throw new \Exception('DOKU API Error: '.$errorMessage);
        }

        $responseData = $response->json();
        $paymentUrl = $responseData['response']['payment']['url'] ?? null;

        if (empty($paymentUrl)) {
            Log::error('DOKU Payment URL Missing', ['response' => $responseData]);
            throw new \Exception('Gagal mendapatkan Payment URL dari DOKU Sandbox.');
        }

        // 9. Kembalikan Payment URL Sandbox string yang didapat dari DOKU
        return $paymentUrl;
    }
}
