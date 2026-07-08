<?php

namespace App\Services;

use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        // Konfigurasi dasar Midtrans menggunakan nilai dari .env
        Config::$serverKey = config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY'));
        Config::$isProduction = (bool) config('services.midtrans.is_production', env('MIDTRANS_IS_PRODUCTION', false));
        Config::$isSanitized = (bool) config('services.midtrans.is_sanitized', env('MIDTRANS_IS_SANITIZED', true));
        Config::$is3ds = (bool) config('services.midtrans.is_3ds', env('MIDTRANS_IS_3DS', true));
    }

    /**
     * Dapatkan Snap Token untuk transaksi Order.
     */
    public function getSnapToken(Order $order, string $paymentType = 'initial'): string
    {
        // 1. Tentukan nominal gross_amount berdasarkan payment_plan atau jenis pembayaran
        $grossAmount = $order->total_amount;

        if ($paymentType === 'initial' && $order->payment_plan === 'dp') {
            // Jika pembayaran awal dan memilih skema DP
            $grossAmount = $order->dp_amount;
        } elseif ($paymentType === 'settlement') {
            // Jika transaksi adalah pelunasan sisa tagihan (remaining)
            // Asumsi model Order memiliki method remaining() atau hitung manual total_amount - amount_paid
            $grossAmount = method_exists($order, 'remaining') ? $order->remaining() : ($order->total_amount - $order->amount_paid);
        }

        // 🔑 GERBANG MUTU: Pastikan gross_amount mutlak integer bulat tanpa desimal
        $grossAmountRounded = intval(round($grossAmount));

        // 2. Buat reference_id yang unik untuk dikirim ke Midtrans
        // Jika pelunasan, tambahkan suffix agar tidak dianggap order duplikat oleh Midtrans
        $orderIdParam = $paymentType === 'settlement'
            ? $order->order_number.'-LUNAS-'.time()
            : $order->order_number;

        // 3. Susun payload standar Midtrans API
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

        // 4. Minta token ke server Midtrans Snap Sandbox
        return Snap::getSnapToken($params);
    }
}
