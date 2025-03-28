<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        // Create providers for some of the users
        User::factory(5)->create()->each(function ($user) {
            Provider::create([
                'user_id' => $user->id,
                'department_id' => null, // Set to null until departments table is created
                'role_id' => rand(1, 3), // Assuming some roles
            ]);
        });
    }
} 