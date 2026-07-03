<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Cakes',
                'description' => 'Delicious birthday, wedding, and slice cakes.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Breads',
                'description' => 'Freshly baked daily bread, buns, and loaves.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Cookies',
                'description' => 'Sweet, crunchy, and soft cookies.',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Pastries',
                'description' => 'Flaky croissants, danishes, and puff pastries.',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Drinks',
                'description' => 'Coffee, tea, and refreshing cold drinks.',
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $cat) {
            Category::create(array_merge($cat, [
                'slug' => Str::slug($cat['name']),
            ]));
        }
    }
}
