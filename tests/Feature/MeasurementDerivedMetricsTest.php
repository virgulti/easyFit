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

test('trends is null without a previous measurement', function () {
    $user = User::factory()->create();

    $measurement = Measurement::factory()->for($user)->create([
        'date' => Carbon::parse('2026-07-01'),
    ]);

    expect($measurement->trends())->toBeNull();
});

test('trends reports direction and color per metric against the previous measurement', function () {
    $user = User::factory()->create();

    Measurement::factory()->for($user)->create([
        'date' => Carbon::parse('2026-06-30'),
        'weight' => 80.0,
        'fat_perc' => 25.0,
        'muscle_perc' => 40.0,
        'bmi_value' => 27.0,
        'bedtime' => '23:00',
        'sleep_minutes' => 420,
    ]);

    $today = Measurement::factory()->for($user)->create([
        'date' => Carbon::parse('2026-07-01'),
        'weight' => 79.0,
        'fat_perc' => 24.0,
        'muscle_perc' => 41.0,
        'bmi_value' => 26.0,
        'bedtime' => '21:00',
        'sleep_minutes' => 480,
    ]);

    $trends = $today->trends();

    // Weight, fat %, BMI, fat weight all dropped: lower is better for all of them.
    expect($trends['weight'])->toBe(['direction' => 'down', 'color' => 'green'])
        ->and($trends['fat_perc'])->toBe(['direction' => 'down', 'color' => 'green'])
        ->and($trends['bmi_value'])->toBe(['direction' => 'down', 'color' => 'green'])
        // Muscle % rose: higher is better.
        ->and($trends['muscle_perc'])->toBe(['direction' => 'up', 'color' => 'green'])
        // Sleep duration rose: higher is better.
        ->and($trends['sleep_minutes'])->toBe(['direction' => 'up', 'color' => 'green'])
        // Bedtime moved earlier (23:00 -> 21:00): reported as "up" (green) per the special
        // earlier-is-better semantics for this metric.
        ->and($trends['bedtime'])->toBe(['direction' => 'up', 'color' => 'green']);
});

test('trends reports stable when the change is smaller than 0.4', function () {
    $user = User::factory()->create();

    Measurement::factory()->for($user)->create([
        'date' => Carbon::parse('2026-06-30'),
        'weight' => 80.0,
    ]);

    $today = Measurement::factory()->for($user)->create([
        'date' => Carbon::parse('2026-07-01'),
        'weight' => 80.2,
    ]);

    expect($today->trends()['weight'])->toBe(['direction' => 'stable', 'color' => 'gray']);
});

test('trends leaves sleep_minutes and bedtime null when either measurement is missing them', function () {
    $user = User::factory()->create();

    Measurement::factory()->for($user)->create([
        'date' => Carbon::parse('2026-06-30'),
        'bedtime' => null,
        'sleep_minutes' => null,
    ]);

    $today = Measurement::factory()->for($user)->create([
        'date' => Carbon::parse('2026-07-01'),
        'bedtime' => '22:00',
        'sleep_minutes' => 450,
    ]);

    $trends = $today->trends();

    expect($trends['bedtime'])->toBeNull()
        ->and($trends['sleep_minutes'])->toBeNull();
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
