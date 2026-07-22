<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Produk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuFilterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unavailable_products_are_not_shown_in_catalog()
    {
        $availableProduct = Produk::factory()->create([
            'nama_produk' => 'Roti Enak',
            'status' => true,
            'is_available' => true,
        ]);

        $unavailableProduct = Produk::factory()->create([
            'nama_produk' => 'Roti Habis',
            'status' => true,
            'is_available' => false,
        ]);

        $response = $this->get(route('menu'));

        $response->assertStatus(200);
        $response->assertSee($availableProduct->nama_produk);
        $response->assertDontSee($unavailableProduct->nama_produk);
    }

    /** @test */
    public function category_filter_returns_only_products_from_that_category()
    {
        $categoryA = Category::factory()->create([
            'slug' => 'kue-basah',
            'is_active' => true,
        ]);
        $categoryB = Category::factory()->create([
            'slug' => 'roti-kering',
            'is_active' => true,
        ]);

        $productA = Produk::factory()->create([
            'nama_produk' => 'Kue Lumpur',
            'category_id' => $categoryA->id,
            'status' => true,
            'is_available' => true,
        ]);
        $productB = Produk::factory()->create([
            'nama_produk' => 'Roti Baguette',
            'category_id' => $categoryB->id,
            'status' => true,
            'is_available' => true,
        ]);

        $responseA = $this->get(route('menu', ['category' => 'kue-basah']));
        $responseA->assertStatus(200);
        $responseA->assertSee($productA->nama_produk);
        $responseA->assertDontSee($productB->nama_produk);

        $responseB = $this->get(route('menu', ['category' => 'roti-kering']));
        $responseB->assertStatus(200);
        $responseB->assertSee($productB->nama_produk);
        $responseB->assertDontSee($productA->nama_produk);
    }

    /** @test */
    public function featured_menu_block_only_displays_featured_products()
    {
        $featuredProduct = Produk::factory()->create([
            'nama_produk' => 'Roti Spesial',
            'status' => true,
            'is_available' => true,
            'is_featured' => true,
        ]);

        $normalProduct = Produk::factory()->create([
            'nama_produk' => 'Roti Biasa',
            'status' => true,
            'is_available' => true,
            'is_featured' => false,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee($featuredProduct->nama_produk);
        $response->assertDontSee($normalProduct->nama_produk);
    }
}
