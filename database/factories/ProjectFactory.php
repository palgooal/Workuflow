<?php

namespace Database\Factories;

use App\Models\User;
use App\Support\Enums\ProjectStatus;
use App\Support\Enums\ProjectType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'name'        => fake()->words(3, true),
            'description' => fake()->sentence(),
            'color'       => fake()->hexColor(),
            'currency'    => 'SAR',
            'type'        => fake()->randomElement(ProjectType::cases())->value,
            'status'      => ProjectStatus::Active->value,
        ];
    }
}
