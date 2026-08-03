<?php

namespace Tests\Feature;

use App\Models\Produk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderSecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function fulfill_at_must_be_at_least_two_hours_in_future()
    {
        $response = $this->post(route('checkout.store'), [
            'customer_name' => 'Pelanggan Test',
            'customer_phone' => '08123456789',
            'customer_email' => 'test@example.com', // 👈 Ditambahkan
            'order_type' => 'pickup',
            'payment_plan' => 'full',
            'fulfill_at' => now()->addHour()->format('Y-m-d H:i:s'), // Kurang dari 2 jam
        ]);

        $response->assertSessionHasErrors('fulfill_at');
    }

    /** @test */
    public function price_manipulation_is_ignored_by_server()
    {
        $produk = Produk::factory()->create([
            'harga' => 50000,
            'is_available' => true,
            'status' => true,
        ]);

        $response = $this->post(route('checkout.store'), [
            'customer_name' => 'Hacker Man',
            'customer_phone' => '08123456789',
            'customer_email' => 'hacker@example.com', // 👈 Ditambahkan agar lolos validasi email
            'order_type' => 'pickup',
            'payment_plan' => 'full',
            'fulfill_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'cart_items' => json_encode([
                [
                    'id' => $produk->id,
                    'name' => $produk->nama_produk,
                    'price' => 500, // Manipulasi harga client
                    'quantity' => 1,
                ],
            ]),
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // Memastikan subtotal dihitung asli dari DB (50.000)
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Hacker Man',
            'subtotal' => 50000.00,
        ]);
    }
}
