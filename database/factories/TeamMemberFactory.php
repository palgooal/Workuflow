<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'name'         => fake()->name(),
            'type'         => fake()->randomElement(['employee', 'freelancer']),
            'specialty'    => fake()->words(2, true),
            'phone'        => fake()->phoneNumber(),
            'email'        => fake()->unique()->safeEmail(),
            'default_rate' => fake()->randomFloat(2, 50, 500),
            'notes'        => null,
            'is_active'    => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function freelancer(): static
    {
        return $this->state(['type' => 'freelancer']);
    }

    public function employee(): static
    {
        return $this->state(['type' => 'employee']);
    }
}
