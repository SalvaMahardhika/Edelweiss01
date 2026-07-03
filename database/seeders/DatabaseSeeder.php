<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            ProdukSeeder::class,
            GaleriSeeder::class,
            OrderSeeder::class,
            ReservationSeeder::class,
        ]);
    }
}
