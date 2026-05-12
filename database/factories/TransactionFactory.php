<?php

namespace Database\Factories;

use App\Models\User;
use App\Support\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'          => User::factory(),
            'project_id'       => null,
            'type'             => fake()->randomElement(TransactionType::cases())->value,
            'amount'           => fake()->randomFloat(2, 10, 5000),
            'currency'         => 'SAR',
            'description'      => fake()->sentence(4),
            'transaction_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
        ];
    }
}
