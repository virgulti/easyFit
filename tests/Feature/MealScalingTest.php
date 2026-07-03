<?php

declare(strict_types=1);

use App\Models\Meal;
use App\Models\User;

test('scaling to the reference weight leaves calories and protein unchanged', function () {
    $meal = Meal::factory()->for(User::factory()->create())->make([
        'reference_weight_grams' => 200,
        'calories' => 400,
        'protein_grams' => 30.5,
    ]);

    expect($meal->scaledTo(200))->toBe(['calories' => 400, 'protein_grams' => 30.5]);
});

test('scaling to a larger weight increases calories and protein proportionally', function () {
    $meal = Meal::factory()->for(User::factory()->create())->make([
        'reference_weight_grams' => 200,
        'calories' => 400,
        'protein_grams' => 30.0,
    ]);

    expect($meal->scaledTo(300))->toBe(['calories' => 600, 'protein_grams' => 45.0]);
});

test('scaling to a smaller weight decreases calories and protein proportionally', function () {
    $meal = Meal::factory()->for(User::factory()->create())->make([
        'reference_weight_grams' => 200,
        'calories' => 400,
        'protein_grams' => 30.0,
    ]);

    expect($meal->scaledTo(100))->toBe(['calories' => 200, 'protein_grams' => 15.0]);
});

test('scaled calories are rounded to the nearest integer', function () {
    $meal = Meal::factory()->for(User::factory()->create())->make([
        'reference_weight_grams' => 100,
        'calories' => 150,
        'protein_grams' => 10.0,
    ]);

    expect($meal->scaledTo(70)['calories'])->toBe(105);
});

test('scaled protein is rounded to one decimal', function () {
    $meal = Meal::factory()->for(User::factory()->create())->make([
        'reference_weight_grams' => 100,
        'calories' => 150,
        'protein_grams' => 10.0,
    ]);

    expect($meal->scaledTo(33)['protein_grams'])->toBe(3.3);
});
