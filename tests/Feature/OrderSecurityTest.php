<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Produk;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrderSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Produk $product;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Buat user untuk simulasi login karena rute dilindungi middleware auth
        $this->user = User::factory()->create();

        // 2. Buat kategori dan produk aktif
        $category = Category::factory()->create(['is_active' => true]);
        $this->product = Produk::create([
            'category_id' => $category->id,
            'nama_produk' => 'Roti Validasi',
            'harga' => 10000,
            'status' => true,
            'is_available' => true,
        ]);

        // 3. Masukkan item ke dalam keranjang belanja
        $cart = $this->app->make(CartService::class);
        $cart->add($this->product->id, 2);
    }

    /** @test */
    public function fulfill_at_must_be_at_least_two_hours_in_future()
    {
        // Kirim request sebagai user aktif
        $response = $this->actingAs($this->user)
            ->post(route('checkout.store'), [
                'customer_name' => 'Salva Mahardhika',
                'customer_phone' => '08123456789',
                'fulfill_at' => Carbon::now()->addHour()->format('Y-m-d H:i:s'), // ❌ Hanya 1 jam ke depan (kurang dari syarat 2 jam)
                'order_type' => 'pickup',
                'payment_plan' => 'full',
            ]);

        $response->assertSessionHasErrors('fulfill_at');
    }

    /** @test */
    public function price_manipulation_is_ignored_by_server()
    {
        // Kirim request lengkap dengan parameter manipulasi harga ilegal
        $response = $this->actingAs($this->user)
            ->post(route('checkout.store'), [
                'customer_name' => 'Salva Mahardhika',
                'customer_phone' => '08123456789',
                'fulfill_at' => Carbon::now()->addHours(3)->format('Y-m-d H:i:s'), // ✅ Aman (3 jam ke depan)
                'order_type' => 'delivery',
                'payment_plan' => 'full',

                // ❌ Upaya manipulasi harga dari sisi klien
                'total_amount' => 999999,
                'subtotal' => 500,
            ]);

        // Pastikan response mengalihkan rute (Redirected) tanpa error validasi
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // 🔑 PERBAIKAN: Ambil order_number langsung dari parameter URL pengalihan (Redirect URL)
        $redirectUrl = $response->headers->get('Location');
        preg_match('/\/checkout\/pay\/([A-Za-z0-9\-]+)/', $redirectUrl, $matches);

        $this->assertNotEmpty($matches, 'Gagal mendeteksi nomor order dari URL redirect pembayaran.');
        $orderNumber = $matches[1];

        // Tarik data order dari database
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        // Hitung ulang ekspektasi harga otoritatif dari server
        $expectedSubtotal = 20000; // 2 items × 10000
        $expectedTax = bcmul((string) $expectedSubtotal, '0.11', 2);
        $expectedTotal = bcadd((string) $expectedSubtotal, $expectedTax, 2);

        // Buktikan jika nominal manipulasi (999999) diabaikan dan server tetap menggunakan hitungan aslinya
        $this->assertEquals($expectedTotal, $order->total_amount);
    }
}
