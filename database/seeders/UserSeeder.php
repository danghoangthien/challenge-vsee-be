<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create a default admin user
        User::create([
            'username' => 'admin',
            'email' => 'danghoangthien@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('VseeAdmin@123456'),
            'firstname' => 'Admin',
            'lastname' => 'User',
            'remember_token' => Str::random(10),
        ]);
    }
} 