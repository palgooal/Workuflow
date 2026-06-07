<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(2, true);
        return [
            'user_id'   => User::factory(),
            'name'      => $name,
            'name_ar'   => $name,
            'color'     => '#6366F1',
            'is_global' => false,
            'is_active' => true,
        ];
    }
}
