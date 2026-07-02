<?php

declare(strict_types=1);

use App\Models\Measurement;
use App\Models\User;
use Illuminate\Support\Carbon;

test('progress is derived from weight using the configured constants', function () {
    $measurement = Measurement::factory()->for(User::factory()->create())->make(['weight' => 80]);

    expect($measurement->progress)->toBe(77.5);
});

test('bmi progress is derived from bmi_value using the configured constants', function () {
    $measurement = Measurement::factory()->for(User::factory()->create())->make(['bmi_value' => 27]);

    expect($measurement->bmiProgress)->toBe(31.5);
});

test('fat weight is weight times fat percentage', function () {
    $measurement = Measurement::factory()->for(User::factory()->create())->make([
        'weight' => 80,
        'fat_perc' => 25,
    ]);

    expect($measurement->fatWeight)->toBe(20.0);
});

test('muscle weight is weight times muscle percentage', function () {
    $measurement = Measurement::factory()->for(User::factory()->create())->make([
        'weight' => 80,
        'muscle_perc' => 40,
    ]);

    expect($measurement->muscleWeight)->toBe(32.0);
});

test('improvement is null without a previous measurement', function () {
    $user = User::factory()->create();

    $measurement = Measurement::factory()->for($user)->create([
        'date' => Carbon::parse('2026-07-01'),
        'bmi_value' => 27,
    ]);

    expect($measurement->improvement())->toBeNull();
});

test('improvement is true when bmi dropped since the previous measurement', function () {
    $user = User::factory()->create();

    Measurement::factory()->for($user)->create([
        'date' => Carbon::parse('2026-06-30'),
        'bmi_value' => 28,
    ]);

    $today = Measurement::factory()->for($user)->create([
        'date' => Carbon::parse('2026-07-01'),
        'bmi_value' => 27,
    ]);

    expect($today->improvement())->toBeTrue();
});

test('improvement is false when bmi rose since the previous measurement', function () {
    $user = User::factory()->create();

    Measurement::factory()->for($user)->create([
        'date' => Carbon::parse('2026-06-30'),
        'bmi_value' => 26,
    ]);

    $today = Measurement::factory()->for($user)->create([
        'date' => Carbon::parse('2026-07-01'),
        'bmi_value' => 27,
    ]);

    expect($today->improvement())->toBeFalse();
});

test('bmi band matches the PLAN reference table', function () {
    $user = User::factory()->create();

    $bands = [
        [17.9, ['label' => 'Underweight', 'color' => 'gray']],
        [18.0, ['label' => 'Normal', 'color' => 'green']],
        [22.9, ['label' => 'Normal', 'color' => 'green']],
        [23.0, ['label' => 'Normal', 'color' => 'yellow']],
        [24.9, ['label' => 'Normal', 'color' => 'yellow']],
        [25.0, ['label' => 'Overweight', 'color' => 'dark-yellow']],
        [28.9, ['label' => 'Overweight', 'color' => 'dark-yellow']],
        [29.0, ['label' => 'Overweight', 'color' => 'orange']],
        [29.9, ['label' => 'Overweight', 'color' => 'orange']],
        [30.0, ['label' => 'Obese', 'color' => 'red']],
        [35.0, ['label' => 'Obese', 'color' => 'red']],
    ];

    foreach ($bands as [$bmi, $expected]) {
        $measurement = Measurement::factory()->for($user)->make(['bmi_value' => $bmi]);

        expect($measurement->bmiBand())->toBe($expected);
    }
});
