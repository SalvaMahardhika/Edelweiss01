<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'type' => fake()->randomElement(['full', 'down_payment', 'settlement']),
            'provider' => 'midtrans',
            'method' => fake()->randomElement(['qris', 'gopay', 'bank_transfer']),
            'amount' => fake()->randomFloat(2, 50000, 200000),
            'currency' => 'IDR',
            'status' => fake()->randomElement(['pending', 'settlement', 'paid', 'failed']),
            'reference' => 'MID-' . fake()->unique()->uuid(),
            'snap_token' => fake()->md5(),
            'payload' => json_encode(['test' => 'data']),
            'paid_at' => now(),
        ];
    }
}
