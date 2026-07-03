<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        if ($customers->isEmpty()) {
            $customers = User::factory(5)->create(['role' => 'customer']);
        }

        $products = Produk::all();
        if ($products->isEmpty()) {
            $this->call(ProdukSeeder::class);
            $products = Produk::all();
        }

        // Create 15 orders
        for ($i = 0; $i < 15; $i++) {
            $customer = $customers->random();

            // Randomly select a final target status
            $targetStatus = fake()->randomElement(['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled']);

            // If the target status is completed, the payment status MUST be paid (lunas)
            if ($targetStatus === 'completed') {
                $paymentStatus = 'paid';
                $paymentPlan = 'full';
            } else {
                $paymentStatus = fake()->randomElement(['unpaid', 'partial', 'paid', 'failed', 'refunded']);
                $paymentPlan = fake()->randomElement(['full', 'dp']);
            }

            // Create order with status = pending initially to prevent validation failure on create/update
            $order = Order::factory()->create([
                'user_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone ?? fake()->phoneNumber(),
                'customer_email' => $customer->email,
                'status' => 'pending',
                'payment_plan' => $paymentPlan,
                'payment_status' => 'unpaid',
                'amount_paid' => 0.00,
            ]);

            // Add 1-4 items
            $itemCount = rand(1, 4);
            $subtotal = 0;
            $selectedProducts = $products->random($itemCount);

            foreach ($selectedProducts as $product) {
                $qty = rand(1, 3);
                $itemSubtotal = $product->harga * $qty;
                $subtotal += $itemSubtotal;

                OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->nama_produk,
                    'unit_price' => $product->harga,
                    'quantity' => $qty,
                    'subtotal' => $itemSubtotal,
                ]);
            }

            $tax = $subtotal * 0.11; // 11% tax
            $total = $subtotal + $tax;

            // Calculate DP amount if payment plan is dp
            $dpAmount = 0;
            if ($paymentPlan === 'dp') {
                $dpAmount = $total * 0.5; // 50% DP
            }

            // Update order amounts
            $order->subtotal = $subtotal;
            $order->tax_amount = $tax;
            $order->total_amount = $total;
            $order->dp_amount = $dpAmount;
            $order->save();

            // Create Payment depending on paymentStatus
            if ($paymentStatus === 'paid') {
                Payment::factory()->create([
                    'order_id' => $order->id,
                    'type' => 'full',
                    'amount' => $total,
                    'status' => 'settlement',
                ]);
            } elseif ($paymentStatus === 'partial') {
                Payment::factory()->create([
                    'order_id' => $order->id,
                    'type' => 'down_payment',
                    'amount' => $dpAmount,
                    'status' => 'settlement',
                ]);
            }

            // Reload payments and recalculate payment status to trigger amount_paid update
            $order->refresh();

            // Finally set the final target status
            $order->status = $targetStatus;
            $order->save();
        }
    }
}
