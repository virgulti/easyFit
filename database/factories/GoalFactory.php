<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Goal>
 */
class GoalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'max_fat_percentage' => fake()->randomFloat(1, 15, 25),
            'min_protein_grams' => fake()->numberBetween(100, 200),
            'max_calories_per_day' => fake()->numberBetween(1200, 2200),
            'max_calories_per_week' => fake()->numberBetween(9000, 15000),
        ];
    }
}
