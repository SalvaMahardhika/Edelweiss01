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

        // 1. Buat user untuk simulasi login
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

        // 3. Masukkan item ke dalam keranjang belanja via CartService
        $cart = $this->app->make(CartService::class);
        $cart->add($this->product->id, 2);
    }

    /** @test */
    public function fulfill_at_must_be_at_least_two_hours_in_future()
    {
        // Kirim request sebagai user aktif dengan waktu di bawah 2 jam
        $response = $this->actingAs($this->user)
            ->post(route('checkout.store'), [
                'customer_name' => 'Salva Mahardhika',
                'customer_phone' => '08123456789',
                'fulfill_at' => Carbon::now()->addHour()->format('Y-m-d H:i:s'), // ❌ Kurang dari 2 jam
                'order_type' => 'pickup',
                'payment_plan' => 'full',
                'cart_items' => json_encode([
                    [
                        'id' => $this->product->id,
                        'quantity' => 2,
                        'unit_price' => 10000,
                    ],
                ]),
            ]);

        $response->assertSessionHasErrors('fulfill_at');
    }

    /** @test */
    public function price_manipulation_is_ignored_by_server()
    {
        // Kirim request lengkap dengan melengkapi delivery_address & cart_items
        $response = $this->actingAs($this->user)
            ->post(route('checkout.store'), [
                'customer_name' => 'Salva Mahardhika',
                'customer_phone' => '08123456789',
                'fulfill_at' => Carbon::now()->addHours(3)->format('Y-m-d H:i:s'), // ✅ 3 jam ke depan
                'order_type' => 'delivery',
                'delivery_address' => 'Jl. Kebon Agung No. 123, Pandaan',
                'payment_plan' => 'full',
                'cart_items' => json_encode([
                    [
                        'id' => $this->product->id,
                        'quantity' => 2,
                        'unit_price' => 10000,
                    ],
                ]),

                // ❌ Upaya manipulasi harga dari sisi klien
                'total_amount' => 999999,
                'subtotal' => 500,
            ]);

        // Pastikan response mengalihkan rute tanpa error validasi
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // Ambil order_number dari URL redirect
        $redirectUrl = $response->headers->get('Location');
        preg_match('/\/checkout\/pay\/([A-Za-z0-9\-]+)/', $redirectUrl, $matches);

        $this->assertNotEmpty($matches, 'Gagal mendeteksi nomor order dari URL redirect pembayaran.');
        $orderNumber = $matches[1];

        // Tarik data order dari database
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        // Hitung ulang ekspektasi harga dari server (2 x 10.000 = 20.000 + PPN 11%)
        $expectedSubtotal = 20000;
        $expectedTax = bcmul((string) $expectedSubtotal, '0.11', 2);
        $expectedTotal = bcadd((string) $expectedSubtotal, $expectedTax, 2);

        // Buktikan manipulasi harga diabaikan
        $this->assertEquals($expectedTotal, $order->total_amount);
    }
}
