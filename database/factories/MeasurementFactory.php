<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Measurement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Measurement>
 */
class MeasurementFactory extends Factory
{
    /**
     * The date to use for the next created measurement, decremented on each use.
     */
    protected static ?Carbon $sequentialDate = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static::$sequentialDate ??= Carbon::today();
        $date = static::$sequentialDate;
        static::$sequentialDate = $date->copy()->subDay();

        return [
            'user_id' => User::factory(),
            'date' => $date,
            'weight' => fake()->randomFloat(1, 70, 100),
            'fat_perc' => fake()->randomFloat(1, 20, 35),
            'muscle_perc' => fake()->randomFloat(1, 25, 40),
            'bmi_value' => fake()->randomFloat(1, 22, 32),
            'bedtime' => fake()->time('H:i'),
            'sleep_minutes' => fake()->numberBetween(300, 540),
            'notes' => null,
        ];
    }
}
