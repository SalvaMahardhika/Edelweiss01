<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProdukFactory extends Factory
{
    protected $model = Produk::class;

    public function definition(): array
    {
        $nama = fake()->words(2, true);

        return [
            'category_id' => Category::factory(),
            'user_id' => User::factory(),
            'nama_produk' => ucfirst($nama),
            'slug' => Str::slug($nama),
            'gambar' => 'produk/'.fake()->image(null, 640, 480, 'food', false),
            'harga' => fake()->randomFloat(2, 10000, 250000),
            'deskripsi' => fake()->paragraph(),
            'status' => true,
            'is_available' => true,
            'is_featured' => fake()->boolean(20), // 20% chance featured
        ];
    }
}
