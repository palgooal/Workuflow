<?php

namespace Database\Factories;

use App\Models\User;
use App\Support\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'    => User::factory(),
            'name'       => fake()->word(),
            'type'       => fake()->randomElement([TransactionType::Income, TransactionType::Expense]),
            'icon'       => '💰',
            'color'      => '#6366F1',
            'is_default' => false,
        ];
    }

    public function income(): static
    {
        return $this->state(['type' => TransactionType::Income]);
    }

    public function expense(): static
    {
        return $this->state(['type' => TransactionType::Expense]);
    }

    public function default(): static
    {
        return $this->state(['is_default' => true]);
    }
}
