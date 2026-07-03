<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        User::factory()->create([
            'name' => 'Super Admin Edelweiss',
            'email' => 'superadmin@edelweiss.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'status' => true,
        ]);

        // Admin
        User::factory()->create([
            'name' => 'Admin Edelweiss',
            'email' => 'admin@edelweiss.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => true,
        ]);

        // Customer
        User::factory()->create([
            'name' => 'Customer Edelweiss',
            'email' => 'customer@edelweiss.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'status' => true,
        ]);

        // Fake Users
        User::factory(10)->create();
    }
}
