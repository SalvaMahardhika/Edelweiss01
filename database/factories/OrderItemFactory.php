<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Produk;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $price = fake()->randomFloat(2, 10000, 100000);
        
        return [
            'order_id' => Order::factory(),
            'product_id' => Produk::factory(),
            'product_name' => fake()->word(),
            'unit_price' => $price,
            'quantity' => $quantity,
            'subtotal' => $price * $quantity,
            'notes' => fake()->sentence(),
        ];
    }
}
