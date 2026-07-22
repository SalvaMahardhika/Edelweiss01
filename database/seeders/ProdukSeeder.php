<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::whereIn('role', ['admin', 'super_admin'])->first() ?? User::factory()->create(['role' => 'admin']);

        $products = [
            'Cakes' => [
                [
                    'nama_produk' => 'Black Forest Cake',
                    'harga' => 150000.00,
                    'deskripsi' => 'Classic chocolate cake with cherry filling and whipped cream.',
                    'gambar' => 'produk/black_forest.jpg',
                ],
                [
                    'nama_produk' => 'Red Velvet Cake',
                    'harga' => 180000.00,
                    'deskripsi' => 'Elegant crimson cake layers with premium cream cheese frosting.',
                    'gambar' => 'produk/red_velvet.jpg',
                ],
                [
                    'nama_produk' => 'Chocolate Fudge Cake',
                    'harga' => 160000.00,
                    'deskripsi' => 'Rich, dense, and moist chocolate cake coated with hot chocolate fudge.',
                    'gambar' => 'produk/chocolate_fudge.jpg',
                ],
            ],
            'Breads' => [
                [
                    'nama_produk' => 'Whole Wheat Bread',
                    'harga' => 22000.00,
                    'deskripsi' => 'Healthy, soft, and high-fiber bread baked with whole wheat flour.',
                    'gambar' => 'produk/whole_wheat.jpg',
                ],
                [
                    'nama_produk' => 'Cheese Bun',
                    'harga' => 12000.00,
                    'deskripsi' => 'Soft bread roll stuffed and topped with melted cheddar cheese.',
                    'gambar' => 'produk/cheese_bun.jpg',
                ],
                [
                    'nama_produk' => 'Garlic Bread',
                    'harga' => 25000.00,
                    'deskripsi' => 'Toasted baguette spread with rich garlic butter and fresh parsley.',
                    'gambar' => 'produk/garlic_bread.jpg',
                ],
            ],
            'Cookies' => [
                [
                    'nama_produk' => 'Choco Chip Cookies',
                    'harga' => 35000.00,
                    'deskripsi' => 'Crispy on the edges, chewy in the middle, packed with dark chocolate chips.',
                    'gambar' => 'produk/choco_chip.jpg',
                ],
                [
                    'nama_produk' => 'Almond Tuiles',
                    'harga' => 45000.00,
                    'deskripsi' => 'Paper-thin, crunchy cookies topped with roasted sliced almonds.',
                    'gambar' => 'produk/almond_tuiles.jpg',
                ],
                [
                    'nama_produk' => 'Nastar Premium',
                    'harga' => 75000.00,
                    'deskripsi' => 'Traditional Indonesian melt-in-the-mouth cookies filled with sweet pineapple jam.',
                    'gambar' => 'produk/nastar.jpg',
                ],
            ],
            'Pastries' => [
                [
                    'nama_produk' => 'Butter Croissant',
                    'harga' => 18000.00,
                    'deskripsi' => 'Flaky, buttery, and multi-layered French pastry.',
                    'gambar' => 'produk/croissant.jpg',
                ],
                [
                    'nama_produk' => 'Chocolate Danish',
                    'harga' => 20000.00,
                    'deskripsi' => 'Puff pastry filled with premium dark chocolate baton.',
                    'gambar' => 'produk/chocolate_danish.jpg',
                ],
                [
                    'nama_produk' => 'Apple Turnover',
                    'harga' => 22000.00,
                    'deskripsi' => 'Sweet spiced apple filling wrapped in flaky puff pastry.',
                    'gambar' => 'produk/apple_turnover.jpg',
                ],
            ],
            'Drinks' => [
                [
                    'nama_produk' => 'Iced Latte',
                    'harga' => 25000.00,
                    'deskripsi' => 'Double shot espresso over cold milk and ice.',
                    'gambar' => 'produk/iced_latte.jpg',
                ],
                [
                    'nama_produk' => 'Hot Cappuccino',
                    'harga' => 24000.00,
                    'deskripsi' => 'Rich espresso with steamed milk and thick layer of foam.',
                    'gambar' => 'produk/cappuccino.jpg',
                ],
            ],
        ];

        foreach ($products as $categoryName => $items) {
            $category = Category::where('name', $categoryName)->first();
            if ($category) {
                foreach ($items as $item) {
                    Produk::create(array_merge($item, [
                        'category_id' => $category->id,
                        'user_id' => $admin->id,
                        'slug' => Str::slug($item['nama_produk']),
                        'status' => true,
                        'is_available' => true,
                        'is_featured' => false,
                    ]));
                }
            }
        }
    }
}
