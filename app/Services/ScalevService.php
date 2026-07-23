<?php

namespace App\Services;

use App\Models\Order;

class ScalevService
{
    /**
     * Mengembalikan URL Hosted Checkout Scalev milik Edelweiss Bakery.
     */
    public function createPaymentUrl(Order $order): string
    {
        // URL utama toko Scalev milikmu
        $baseUrl = "https://edelweiss-bakery.myscalev.com";

        // Meneruskan parameter nomor pesanan & nominal agar pelanggan tidak perlu isi ulang
        $queryParameters = http_build_query([
            'order_number' => $order->order_number,
            'name' => $order->customer_name,
            'phone' => $order->customer_phone,
            'email' => $order->customer_email ?? '',
        ]);

        return "{$baseUrl}?{$queryParameters}";
    }
}