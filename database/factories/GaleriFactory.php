<?php

namespace Database\Factories;

use App\Models\Galeri;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GaleriFactory extends Factory
{
    protected $model = Galeri::class;

    public function definition(): array
    {
        return [
            'judul' => fake()->sentence(3),
            'album' => fake()->randomElement(['Cake', 'Bread', 'Cookies', 'Pastry']),
            'deskripsi' => fake()->paragraph(),
            'user_id' => User::factory(),
        ];
    }
}
