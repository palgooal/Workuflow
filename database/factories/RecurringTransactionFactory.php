<?php

namespace Database\Factories;

use App\Models\User;
use App\Support\Enums\RecurringFrequency;
use App\Support\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecurringTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'category_id'   => null,
            'project_id'    => null,
            'type'          => TransactionType::Expense,
            'amount'        => fake()->randomFloat(2, 100, 5000),
            'currency'      => 'SAR',
            'description'   => fake()->sentence(3),
            'frequency'     => RecurringFrequency::Monthly,
            'start_date'    => now()->toDateString(),
            'next_due_date' => now()->addMonth()->toDateString(),
            'end_date'      => null,
            'is_active'     => true,
        ];
    }

    public function income(): static
    {
        return $this->state(['type' => TransactionType::Income]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
