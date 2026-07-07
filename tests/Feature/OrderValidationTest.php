<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentPlan;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrderValidationTest extends TestCase
{
    use RefreshDatabase; // Mengosongkan DB testing setiap kali test dijalankan

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat user admin untuk simulasi login
        $this->admin = User::create([
            'name' => 'Salva Mahardhika',
            'email' => 'salva@edelweiss.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 1,
        ]);
    }

    /** @test */
    public function tanggal_fulfill_wajib_di_masa_depan()
    {
        // 1. Jalankan sebagai admin yang sudah login
        $response = $this->actingAs($this->admin)
            ->post(route('orders.store'), [
                'customer_name' => 'Pelanggan Edelweiss',
                'customer_phone' => '08123456789',
                'payment_plan' => 'full',
                'fulfill_at' => Carbon::now()->subDay()->format('Y-m-d H:i:s'), // ❌ Mengisi tanggal kemarin (masa lalu)
            ]);

        // 2. HTTP harus mengembalikan error validasi (422 / Redirect back dengan error)
        $response->assertSessionHasErrors(['fulfill_at']);
    }

    /** @test */
    public function nominal_dp_harus_antara_10_persen_hingga_90_persen()
    {
        // 1. Jalankan sebagai admin dengan nominal DP cuma 5% (di bawah batas 10%)
        $response = $this->actingAs($this->admin)
            ->post(route('orders.store'), [
                'customer_name' => 'Pelanggan Edelweiss 2',
                'customer_phone' => '08123456789',
                'payment_plan' => 'dp',
                'total_amount' => 100000, // Total Rp 100.000
                'dp_amount' => 5000,   // ❌ Hanya Rp 5.000 (5%), harusnya minimal Rp 10.000 (10%)
                'fulfill_at' => Carbon::now()->addDays(5)->format('Y-m-d H:i:s'), // Tanggal aman di masa depan
            ]);

        // 2. HTTP harus menolak dan mendeteksi eror pada field dp_amount
        $response->assertSessionHasErrors(['dp_amount']);
    }

    /** @test */
    public function order_berhasil_dibuat_jika_data_valid()
    {
        // 1. Kirim data yang 100% benar dan valid
        $response = $this->actingAs($this->admin)
            ->post(route('orders.store'), [
                'customer_name' => 'Pelanggan Sukses',
                'customer_phone' => '08123456789',
                'payment_plan' => 'full',
                'fulfill_at' => Carbon::now()->addDays(3)->format('Y-m-d H:i:s'), // ✅ 3 hari ke depan
            ]);

        // 2. Sesi harus sukses tanpa ada eror validasi
        $response->assertSessionHasNoErrors();

        // 3. Pastikan data benar-benar masuk ke database tabel orders
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Pelanggan Sukses',
            'payment_plan' => 'full',
        ]);
    }

    /** @test */
    public function proses_pelunasan_order_dp_setelah_fulfill_plus_satu_hari_harus_paid_tanpa_exception()
    {
        // 1. Setup waktu awal (Sekarang) dan tanggal fulfillment (misal 3 hari ke depan)
        $waktuSkarang = Carbon::now();
        $tanggalFulfill = $waktuSkarang->copy()->addDays(3);

        // 2. Buat data order DP awal secara langsung ke DB
        $order = Order::create([
            'order_number' => 'EDL-20260707-TEST',
            'user_id' => $this->admin->id,
            'customer_name' => 'Pelanggan Bayar DP',
            'customer_phone' => '08123456789',
            'status' => OrderStatus::Pending ?? 'pending',
            'payment_plan' => PaymentPlan::Dp ?? 'dp',
            'payment_status' => PaymentStatus::Partial ?? 'partial',
            'subtotal' => 90090.09,
            'tax_amount' => 9909.91,
            'total_amount' => 100000.00,
            'dp_amount' => 50000.00,
            'amount_paid' => 50000.00, // Sudah bayar DP setengahnya
            'fulfill_at' => $tanggalFulfill,
            'placed_at' => $waktuSkarang,
        ]);

        // Catat payment awal untuk DP-nya agar sinkron di database relasi
        Payment::create([
            'order_id' => $order->id,
            'type' => 'down_payment',
            'amount' => 50000.00,
            'status' => 'settlement',
            'reference' => 'REF-DP-'.uniqid(),
        ]);

        // 3. ⏰ MANIPULASI WAKTU: Set waktu testing melompat ke H+1 setelah tanggal fulfill
        $hariPelunasan = $tanggalFulfill->copy()->addDay();
        Carbon::setTestNow($hariPelunasan);

        // 4. Lakukan transaksi pelunasan sisa tagihan (50.000)
        try {
            // Membuat records payment baru untuk sisa pelunasan
            $pelunasan = Payment::create([
                'order_id' => $order->id,
                'type' => 'settlement',
                'amount' => 50000.00, // Sisa pelunasan 100rb - 50rb
                'status' => 'settlement',
                'reference' => 'REF-LUNAS-'.uniqid(),
            ]);

            // Picu fungsi hitung ulang bawaan model Order kamu untuk meng-update status ke Paid
            $order->recalculatePaymentStatus();

        } catch (\Exception $e) {
            $this->fail('Proses pelunasan melempar exception tak terduga: '.$e->getMessage());
        }

        // 5. ASSERTION: Pastikan status pembayaran berubah menjadi Paid dan tidak melempar Exception
        $this->assertEquals(PaymentStatus::Paid ?? 'paid', $order->fresh()->payment_status);

        // 6. Bersihkan kembali manipulasi waktu Carbon setelah pengujian selesai
        Carbon::setTestNow();
    }
}
