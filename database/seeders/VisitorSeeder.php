<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Visitor;
use Illuminate\Database\Seeder;

class VisitorSeeder extends Seeder
{
    public function run(): void
    {
        // Create visitors for some of the users
        User::factory(8)->create()->each(function ($user) {
            Visitor::create([
                'user_id' => $user->id,
            ]);
        });
    }
} 