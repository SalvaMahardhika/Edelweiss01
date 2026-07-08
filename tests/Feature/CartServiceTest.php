<?php

namespace Tests\Feature;

use App\Models\Produk;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = new CartService;
    }

    /** @test */
    public function it_can_add_update_remove_and_clear_items_in_cart()
    {
        $product = Produk::factory()->create([
            'harga' => 10000.00,
            'is_available' => true,
        ]);

        // Test Add
        $this->cartService->add($product->id, 2, 'Catatan tambahan');

        $this->assertTrue(session()->has('cart'));
        $this->assertEquals(2, session('cart')[$product->id]['quantity']);
        $this->assertEquals('Catatan tambahan', session('cart')[$product->id]['notes']);

        // Test Add accumulation
        $this->cartService->add($product->id, 3);
        $this->assertEquals(5, session('cart')[$product->id]['quantity']);

        // Test Update
        $this->cartService->update($product->id, 10);
        $this->assertEquals(10, session('cart')[$product->id]['quantity']);

        // Test Update to <= 0 removes it
        $this->cartService->update($product->id, 0);
        $this->assertArrayNotHasKey($product->id, session('cart', []));

        // Add back and test remove
        $this->cartService->add($product->id, 1);
        $this->cartService->remove($product->id);
        $this->assertArrayNotHasKey($product->id, session('cart', []));

        // Test Clear
        $this->cartService->add($product->id, 1);
        $this->cartService->clear();
        $this->assertFalse(session()->has('cart'));
    }

    /** @test */
    public function unavailable_products_are_excluded_from_cart_items()
    {
        $availableProduct = Produk::factory()->create([
            'nama_produk' => 'Roti Enak',
            'is_available' => true,
        ]);

        $unavailableProduct = Produk::factory()->create([
            'nama_produk' => 'Roti Habis',
            'is_available' => false,
        ]);

        $this->cartService->add($availableProduct->id, 2);
        $this->cartService->add($unavailableProduct->id, 3);

        $items = $this->cartService->items();

        $this->assertCount(1, $items);
        $this->assertEquals($availableProduct->id, $items->first()['product_id']);
    }

    /** @test */
    public function cart_prices_and_subtotals_are_always_calculated_from_database_and_ignore_session_manipulation()
    {
        $product = Produk::factory()->create([
            'harga' => 15000.00,
            'is_available' => true,
        ]);

        // Add product to cart
        $this->cartService->add($product->id, 2);

        // Manipulate session data manually to simulate client-side injection / tampering
        $cart = session()->get('cart');
        $cart[$product->id]['harga'] = 500.00;
        $cart[$product->id]['unit_price'] = 500.00;
        $cart[$product->id]['subtotal'] = 1000.00;
        session()->put('cart', $cart);

        // Fetch items and check if calculations are still based on DB price (15000.00)
        $items = $this->cartService->items();

        $this->assertCount(1, $items);
        $item = $items->first();

        // unit_price must be database price, not session price
        $this->assertEquals('15000.00', $item['unit_price']);
        // subtotal must be database price * quantity (15000.00 * 2 = 30000.00)
        $this->assertEquals('30000.00', $item['subtotal']);
    }
}
