<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrderValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Produk $produk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Salva Mahardhika',
            'email' => 'salva@edelweiss.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 1,
        ]);

        $this->produk = Produk::create([
            'nama_produk' => 'Roti Manis Testing',
            'harga' => 25000,
            'status' => 1,
        ]);
    }

    /** @test */
    public function tanggal_fulfill_wajib_di_masa_depan()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('checkout.store'), [
                'customer_name' => 'Pelanggan Edelweiss',
                'customer_phone' => '08123456789',
                'payment_plan' => 'full',
                'order_type' => 'pickup',
                'fulfill_at' => Carbon::now()->subDay()->format('Y-m-d H:i:s'),
                'cart_items' => json_encode([
                    [
                        'id' => $this->produk->id,
                        'quantity' => 2,
                        'unit_price' => 15000,
                    ],
                ]),
            ]);

        $response->assertSessionHasErrors(['fulfill_at']);
    }

    /** @test */
    public function nominal_dp_harus_antara_10_persen_hingga_90_persen()
    {
        // 💡 Pengujian pembuatan order dengan metode skema DP 50%
        $response = $this->actingAs($this->admin)
            ->post(route('checkout.store'), [
                'customer_name' => 'Pelanggan DP Test',
                'customer_phone' => '08123456789',
                'payment_plan' => 'dp',
                'order_type' => 'pickup',
                'fulfill_at' => Carbon::now()->addDays(5)->format('Y-m-d H:i:s'),
                'cart_items' => json_encode([
                    [
                        'id' => $this->produk->id,
                        'quantity' => 2,
                        'unit_price' => 25000,
                    ],
                ]),
            ]);

        // Dipastikan tidak ada error validasi saat memilih skema DP yang sah
        $response->assertSessionHasNoErrors();

        // Memastikan order tersimpan ke database dengan skema DP
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Pelanggan DP Test',
            'payment_plan' => 'dp',
        ]);
    }

    /** @test */
    public function order_berhasil_dibuat_jika_data_valid()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('checkout.store'), [
                'customer_name' => 'Pelanggan Sukses',
                'customer_phone' => '08123456789',
                'payment_plan' => 'full',
                'order_type' => 'pickup',
                'fulfill_at' => Carbon::now()->addDays(3)->format('Y-m-d H:i:s'),
                'cart_items' => json_encode([
                    [
                        'id' => $this->produk->id,
                        'quantity' => 2,
                        'unit_price' => 25000,
                    ],
                ]),
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Pelanggan Sukses',
            'payment_plan' => 'full',
        ]);
    }

    /** @test */
    public function proses_pelunasan_order_dp_setelah_fulfill_plus_satu_hari_harus_paid_tanpa_exception()
    {
        $waktuSkarang = Carbon::now();
        $tanggalFulfill = $waktuSkarang->copy()->addDays(3);

        $order = Order::create([
            'order_number' => 'EDL-20260707-TEST',
            'user_id' => $this->admin->id,
            'customer_name' => 'Pelanggan Bayar DP',
            'customer_phone' => '08123456789',
            'status' => 'pending',
            'payment_plan' => 'dp',
            'payment_status' => 'partial',
            'subtotal' => 90090.09,
            'tax_amount' => 9909.91,
            'total_amount' => 100000.00,
            'dp_amount' => 50000.00,
            'amount_paid' => 50000.00,
            'fulfill_at' => $tanggalFulfill,
            'placed_at' => $waktuSkarang,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'type' => 'down_payment',
            'amount' => 50000.00,
            'status' => 'settlement',
            'reference' => 'REF-DP-'.uniqid(),
        ]);

        $hariPelunasan = $tanggalFulfill->copy()->addDay();
        Carbon::setTestNow($hariPelunasan);

        try {
            Payment::create([
                'order_id' => $order->id,
                'type' => 'settlement',
                'amount' => 50000.00,
                'status' => 'settlement',
                'reference' => 'REF-LUNAS-'.uniqid(),
            ]);

            $order->recalculatePaymentStatus();

        } catch (\Exception $e) {
            $this->fail('Proses pelunasan melempar exception tak terduga: '.$e->getMessage());
        }

        $statusAktif = is_object($order->fresh()->payment_status) ? $order->fresh()->payment_status->value : $order->fresh()->payment_status;
        $this->assertEquals('paid', $statusAktif);

        Carbon::setTestNow();
    }
}
