<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'category_id' => null,
            'project_id'  => null,
            'amount'      => fake()->randomFloat(2, 500, 10000),
            'period'      => 'monthly',
            'month'       => now()->month,
            'year'        => now()->year,
        ];
    }

    public function yearly(): static
    {
        return $this->state(['period' => 'yearly', 'month' => null]);
    }
}
