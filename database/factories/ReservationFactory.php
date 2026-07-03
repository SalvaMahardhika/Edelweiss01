<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->phoneNumber(),
            'reserved_at' => fake()->dateTimeBetween('now', '+7 days'),
            'party_size' => fake()->numberBetween(1, 10),
            'status' => fake()->randomElement(['pending', 'confirmed', 'seated', 'completed', 'cancelled']),
            'table_number' => 'T-' . fake()->numberBetween(1, 15),
            'notes' => fake()->sentence(),
        ];
    }
}
