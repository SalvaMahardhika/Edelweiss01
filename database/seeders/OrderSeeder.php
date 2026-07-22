<?php

namespace Database\Seeders;

use App\Models\Produk;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Pastikan data customer tersedia
        $customers = User::where('role', 'customer')->get();
        if ($customers->isEmpty()) {
            $customers = User::factory(5)->create(['role' => 'customer']);
        }

        // 2. Panggil ProdukSeeder secara aman untuk memastikan tabel produk terisi
        $this->call(ProdukSeeder::class);
        $products = Produk::all();

        // Jika setelah dipanggil ternyata masih kosong, buat dummy produk instan agar tidak crash
        if ($products->isEmpty()) {
            DB::table('produk')->insert([
                'nama_produk' => 'Roti Dummy',
                'harga' => 20000.00,
                'status' => true,
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $products = Produk::all();
        }

        // ==========================================
        // LOOP DATA INDUK ACAK (15 ORDERS) VIA DB BUILDER
        // ==========================================
        for ($i = 0; $i < 15; $i++) {
            $customer = $customers->random();
            $targetStatus = fake()->randomElement(['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled']);

            if ($targetStatus === 'completed') {
                $paymentStatus = 'paid';
                $paymentPlan = 'full';
            } else {
                $paymentStatus = fake()->randomElement(['unpaid', 'partial', 'paid', 'failed', 'refunded']);
                $paymentPlan = fake()->randomElement(['full', 'dp']);
            }

            // Hitung subtotal bayangan
            $itemCount = min(rand(1, 4), $products->count());
            $selectedProducts = $products->random($itemCount);

            $subtotal = 0;
            foreach ($selectedProducts as $product) {
                $qty = rand(1, 3);
                $subtotal += ($product->harga * $qty);
            }

            $tax = $subtotal * 0.11;
            $total = $subtotal + $tax;
            $dpAmount = ($paymentPlan === 'dp') ? ($total * 0.50) : 0;

            $amountPaid = 0;
            if ($paymentStatus === 'paid') {
                $amountPaid = $total;
            } elseif ($paymentStatus === 'partial') {
                $amountPaid = $dpAmount;
            }

            // 🔑 BYPASS MODEL: Gunakan DB::table agar terhindar dari DomainException / saving validation
            $orderId = DB::table('orders')->insertGetId([
                'order_number' => 'EDL-'.Carbon::now()->format('Ymd').'-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'user_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone ?? '08123456789',
                'customer_email' => $customer->email,
                'order_type' => 'pickup',
                'status' => $targetStatus,
                'payment_plan' => $paymentPlan,
                'payment_status' => $paymentStatus,
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'dp_amount' => $dpAmount,
                'amount_paid' => $amountPaid,
                'fulfill_at' => Carbon::now()->addDays(2),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Buat order items menggunakan DB::table
            foreach ($selectedProducts as $product) {
                $qty = rand(1, 3);
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $product->id,
                    'product_name' => $product->nama_produk,
                    'unit_price' => $product->harga,
                    'quantity' => $qty,
                    'subtotal' => $product->harga * $qty,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Buat payment log menggunakan DB::table
            if ($amountPaid > 0) {
                DB::table('payments')->insert([
                    'order_id' => $orderId,
                    'type' => ($paymentPlan === 'dp') ? 'down_payment' : 'full',
                    'provider' => 'midtrans',
                    'method' => 'qris',
                    'amount' => $amountPaid,
                    'status' => 'settlement',
                    'reference' => 'B-TX-DUMMY-'.uniqid(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ==========================================
        // 🔔 DATA KHUSUS INJEKSI TESTING (SUB-TASK 6)
        // ==========================================
        DB::table('orders')->where('order_number', 'EDL-WEBHOOK-TEST')->delete();

        DB::table('orders')->insert([
            'order_number' => 'EDL-WEBHOOK-TEST',
            'user_id' => null,
            'customer_name' => 'Salva Mahardhika',
            'customer_phone' => '08123456789',
            'customer_email' => 'salva@example.com',
            'order_type' => 'pickup',
            'status' => 'partial',
            'payment_plan' => 'dp',
            'payment_status' => 'partial',
            'subtotal' => 100000.00,
            'tax_amount' => 0.00,
            'total_amount' => 100000.00,
            'dp_amount' => 30000.00,
            'amount_paid' => 30000.00,
            'fulfill_at' => Carbon::now()->addDays(2)->setTime(10, 0, 0),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->command->info('🎉 Seeder Utama Sukses Menyuntikkan Data Tanpa Interupsi Model!');
    }
}
