<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentPlan;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Membuat Order baru dengan menghitung subtotal & total_amount langsung dari harga produk di database.
     * Mengabaikan harga apapun yang dikirimkan dari browser.
     *
     * @param  array  $itemsData  Array berisi [['product_id' => x, 'quantity' => y], ...]
     */
    public function createOrder(array $orderData, array $itemsData): Order
    {
        return DB::transaction(function () use ($orderData, $itemsData) {
            // 1. Tentukan nomor order unik jika belum diset
            $orderNumber = $orderData['order_number'] ?? 'EDL-'.now()->format('Ymd').'-'.rand(1000, 9999);

            // 2. Buat instance Order dengan nilai default, pastikan status awal pending
            $order = Order::create(array_merge($orderData, [
                'order_number' => $orderNumber,
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Unpaid,
                'subtotal' => 0.00,
                'tax_amount' => 0.00,
                'total_amount' => 0.00,
                'dp_amount' => 0.00,
                'amount_paid' => 0.00,
            ]));

            $subtotal = '0.00';

            // 3. Masukkan item-item belanjaan dengan harga asli dari DB produk
            foreach ($itemsData as $itemData) {
                $product = Produk::findOrFail($itemData['product_id']);

                // Ambil harga fresh dari database, bukan dari input browser/klien
                $price = $product->harga;
                $qty = $itemData['quantity'];
                $itemSubtotal = bcmul((string) $price, (string) $qty, 2);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->nama_produk,
                    'unit_price' => $price,
                    'quantity' => $qty,
                    'subtotal' => $itemSubtotal,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                $subtotal = bcadd($subtotal, $itemSubtotal, 2);
            }

            // 4. Hitung pajak 11% dan total pembayaran
            $taxAmount = bcmul($subtotal, '0.11', 2);
            $totalAmount = bcadd($subtotal, $taxAmount, 2);

            // 5. Tentukan dp_amount (50%) jika menggunakan paket DP
            $paymentPlan = $orderData['payment_plan'] ?? PaymentPlan::Full;
            $dpAmount = ($paymentPlan === PaymentPlan::Dp || $paymentPlan === 'dp')
                ? bcmul($totalAmount, '0.50', 2)
                : '0.00';

            // 6. Update order dengan total yang dihitung di server
            $order->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'dp_amount' => $dpAmount,
            ]);

            // Jika status final ditentukan pada parameter input, set setelah order ter-update & paid dihitung
            if (isset($orderData['status'])) {
                $order->status = $orderData['status'];
                $order->save();
            }

            return $order;
        });
    }
}
