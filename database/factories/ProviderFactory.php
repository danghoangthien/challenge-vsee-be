<?php

namespace Database\Factories;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProviderFactory extends Factory
{
    protected $model = Provider::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'department_id' => 1, // Default department ID
            'role_id' => 1, // Default role ID
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
} 