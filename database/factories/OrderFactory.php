<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled']);
        $paymentStatus = fake()->randomElement(['unpaid', 'partial', 'paid', 'failed', 'refunded']);
        $orderType = fake()->randomElement(['pickup', 'delivery']);
        $paymentPlan = fake()->randomElement(['full', 'dp']);

        return [
            'order_number' => 'EDL-'.now()->format('Ymd').'-'.fake()->unique()->numberBetween(1000, 9999),
            'user_id' => User::factory(),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->phoneNumber(),
            'customer_email' => fake()->safeEmail(),
            'order_type' => $orderType,
            'status' => $status,
            'payment_plan' => $paymentPlan,
            'payment_status' => $paymentStatus,
            'subtotal' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'dp_amount' => $paymentPlan === 'dp' ? fake()->randomFloat(2, 50000, 100000) : 0,
            'amount_paid' => $paymentStatus === 'paid' ? 100000 : 0,
            'fulfill_at' => fake()->dateTimeBetween('now', '+7 days'),
            'settlement_due_at' => fake()->dateTimeBetween('now', '+3 days'),
            'notes' => fake()->sentence(),
            'placed_at' => now(),
        ];
    }
}
