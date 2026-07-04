<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Meal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meal>
 */
class MealFactory extends Factory
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
            'description' => rtrim(fake()->unique()->sentence(3), '.'),
            'reference_weight_grams' => fake()->numberBetween(50, 500),
            'calories' => fake()->numberBetween(80, 800),
            'protein_grams' => fake()->randomFloat(1, 2, 50),
        ];
    }
}
