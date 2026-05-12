<?php

namespace Database\Factories;

use App\Models\User;
use App\Support\Enums\DebtStatus;
use App\Support\Enums\DebtType;
use Illuminate\Database\Eloquent\Factories\Factory;

class DebtFactory extends Factory
{
    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 100, 10000);

        return [
            'user_id'          => User::factory(),
            'type'             => fake()->randomElement(DebtType::cases())->value,
            'party_name'       => fake()->name(),
            'amount'           => $amount,
            'remaining_amount' => $amount,
            'currency'         => 'SAR',
            'due_date'         => fake()->optional()->dateTimeBetween('now', '+3 months')?->format('Y-m-d'),
            'status'           => DebtStatus::Active->value,
            'notes'            => null,
        ];
    }
}
