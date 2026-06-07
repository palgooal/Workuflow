<?php

namespace Database\Factories;

use App\Models\User;
use App\Support\Enums\WalletType;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'         => User::factory(),
            'name'            => fake()->randomElement(['الصندوق النقدي', 'حساب البنك', 'محفظة STC Pay', 'كاش المكتب']),
            'type'            => fake()->randomElement(WalletType::cases()),
            'currency'        => 'SAR',
            'initial_balance' => fake()->randomFloat(2, 0, 5000),
            'color'           => fake()->randomElement(['#6366f1', '#10b981', '#f59e0b', '#3b82f6']),
            'icon'            => fake()->randomElement(['💵', '🏦', '📦', '💼']),
            'description'     => null,
            'is_active'       => true,
        ];
    }

    public function cash(): static
    {
        return $this->state(['type' => WalletType::Cash, 'icon' => '💵']);
    }

    public function bank(): static
    {
        return $this->state(['type' => WalletType::Bank, 'icon' => '🏦']);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
