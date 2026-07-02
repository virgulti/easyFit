<?php

use App\Models\Measurement;
use App\Models\User;
use App\Services\WeeklyAverageService;
use Carbon\Carbon;

it('calculates weekly averages correctly', function () {
    $user = User::factory()->create();

    // Create measurements spanning 3 ISO weeks with at least one gap in one of the weeks
    Measurement::factory()->for($user)->create([
        'date' => Carbon::parse('2024-01-01'),
        'weight' => 80.0,
        'fat_perc' => 25.0,
        'muscle_perc' => 30.0,
        'bmi_value' => 27.0,
    ]);

    Measurement::factory()->for($user)->create([
        'date' => Carbon::parse('2024-01-02'),
        'weight' => 81.0,
        'fat_perc' => 26.0,
        'muscle_perc' => 31.0,
        'bmi_value' => 27.5,
    ]);

    Measurement::factory()->for($user)->create([
        'date' => Carbon::parse('2024-01-08'),
        'weight' => 82.0,
        'fat_perc' => 27.0,
        'muscle_perc' => 32.0,
        'bmi_value' => 28.0,
    ]);

    Measurement::factory()->for($user)->create([
        'date' => Carbon::parse('2024-01-09'),
        'weight' => 83.0,
        'fat_perc' => 28.0,
        'muscle_perc' => 33.0,
        'bmi_value' => 28.5,
    ]);

    Measurement::factory()->for($user)->create([
        'date' => Carbon::parse('2024-01-15'),
        'weight' => 84.0,
        'fat_perc' => 29.0,
        'muscle_perc' => 34.0,
        'bmi_value' => 29.0,
    ]);

    Measurement::factory()->for($user)->create([
        'date' => Carbon::parse('2024-01-16'),
        'weight' => 85.0,
        'fat_perc' => 30.0,
        'muscle_perc' => 35.0,
        'bmi_value' => 29.5,
    ]);

    $service = new WeeklyAverageService;
    $averages = $service->averagesForUser($user);

    expect($averages)->toHaveCount(3)
        ->and($averages[0]['week_start'])->toBe('2024-01-01')
        ->and($averages[0]['week_end'])->toBe('2024-01-07')
        ->and($averages[0]['days_count'])->toBe(2)
        ->and($averages[0]['weight'])->toBe(80.5)
        ->and($averages[0]['fat_perc'])->toBe(25.5)
        ->and($averages[0]['muscle_perc'])->toBe(30.5)
        ->and($averages[0]['bmi_value'])->toBe(27.25)
        // progress/bmi_progress accessors round to 1 decimal, fat_weight/muscle_weight to 2,
        // before this service averages and rounds again to 2 decimals.
        // progress = (85 - weight) * 1.5 + 70 => 77.5, 76.0
        ->and($averages[0]['progress'])->toBe(round((77.5 + 76.0) / 2, 2))
        // bmi_progress = (30 - bmi) * 4.5 + 18 => 31.5, round(29.25, 1) = 29.3
        ->and($averages[0]['bmi_progress'])->toBe(round((31.5 + 29.3) / 2, 2))
        // fat_weight = weight * fat_perc / 100 => 20.0, 21.06
        ->and($averages[0]['fat_weight'])->toBe(round((20.0 + 21.06) / 2, 2))
        // muscle_weight = weight * muscle_perc / 100 => 24.0, 25.11
        ->and($averages[0]['muscle_weight'])->toBe(round((24.0 + 25.11) / 2, 2))
        ->and($averages[1]['week_start'])->toBe('2024-01-08')
        ->and($averages[1]['week_end'])->toBe('2024-01-14')
        ->and($averages[1]['days_count'])->toBe(2)
        ->and($averages[1]['weight'])->toBe(82.5)
        ->and($averages[1]['fat_perc'])->toBe(27.5)
        ->and($averages[1]['muscle_perc'])->toBe(32.5)
        ->and($averages[1]['bmi_value'])->toBe(28.25)
        ->and($averages[2]['week_start'])->toBe('2024-01-15')
        ->and($averages[2]['week_end'])->toBe('2024-01-21')
        ->and($averages[2]['days_count'])->toBe(2)
        ->and($averages[2]['weight'])->toBe(84.5)
        ->and($averages[2]['fat_perc'])->toBe(29.5)
        ->and($averages[2]['muscle_perc'])->toBe(34.5)
        ->and($averages[2]['bmi_value'])->toBe(29.25);
});
