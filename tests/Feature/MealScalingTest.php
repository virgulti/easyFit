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

    expect($meal->scaledTo(200))->toBe(['calories' => 400, 'protein_grams' => 30.5, 'cost' => null]);
});

test('scaling to a larger weight increases calories and protein proportionally', function () {
    $meal = Meal::factory()->for(User::factory()->create())->make([
        'reference_weight_grams' => 200,
        'calories' => 400,
        'protein_grams' => 30.0,
    ]);

    expect($meal->scaledTo(300))->toBe(['calories' => 600, 'protein_grams' => 45.0, 'cost' => null]);
});

test('scaling to a smaller weight decreases calories and protein proportionally', function () {
    $meal = Meal::factory()->for(User::factory()->create())->make([
        'reference_weight_grams' => 200,
        'calories' => 400,
        'protein_grams' => 30.0,
    ]);

    expect($meal->scaledTo(100))->toBe(['calories' => 200, 'protein_grams' => 15.0, 'cost' => null]);
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

test('cost scales proportionally when the catalog meal has a reference cost', function () {
    $meal = Meal::factory()->for(User::factory()->create())->make([
        'reference_weight_grams' => 100,
        'reference_cost' => 4.0,
    ]);

    expect($meal->scaledTo(250)['cost'])->toBe(10.0);
});

test('scaled cost is rounded to two decimals', function () {
    $meal = Meal::factory()->for(User::factory()->create())->make([
        'reference_weight_grams' => 100,
        'reference_cost' => 3.33,
    ]);

    expect($meal->scaledTo(70)['cost'])->toBe(2.33);
});

test('scaled cost is null when the catalog meal has no reference cost', function () {
    $meal = Meal::factory()->for(User::factory()->create())->make([
        'reference_cost' => null,
    ]);

    expect($meal->scaledTo(123)['cost'])->toBeNull();
});
