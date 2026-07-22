<?php

namespace Database\Seeders;

use App\Models\Galeri;
use App\Models\User;
use Illuminate\Database\Seeder;

class GaleriSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::whereIn('role', ['admin', 'super_admin'])->first() ?? User::factory()->create(['role' => 'admin']);

        $albums = ['Cakes', 'Grand Opening', 'Daily Bakery', 'Kitchen Love'];

        foreach ($albums as $album) {
            Galeri::factory(3)->create([
                'album' => $album,
                'user_id' => $admin->id,
            ]);
        }
    }
}
