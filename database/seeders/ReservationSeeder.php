<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        if ($customers->isEmpty()) {
            $customers = User::factory(5)->create(['role' => 'customer']);
        }

        for ($i = 0; $i < 10; $i++) {
            $customer = $customers->random();
            Reservation::factory()->create([
                'user_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone ?? fake()->phoneNumber(),
            ]);
        }
    }
}
