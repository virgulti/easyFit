<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MealType;
use App\Models\MealLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MealLog>
 */
class MealLogFactory extends Factory
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
            'meal_id' => null,
            'description' => rtrim(fake()->unique()->sentence(3), '.'),
            'meal_type' => fake()->randomElement(MealType::cases()),
            'weight_grams' => fake()->numberBetween(50, 500),
            'calories' => fake()->numberBetween(80, 800),
            'protein_grams' => fake()->randomFloat(1, 2, 50),
            'cost' => fake()->optional(0.7)->randomFloat(2, 1, 25),
            'date' => fake()->dateTimeBetween('-90 days', 'now'),
        ];
    }
}
