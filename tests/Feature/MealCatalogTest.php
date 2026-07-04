<?php

declare(strict_types=1);

use App\Models\Meal;
use App\Models\User;

test('a user cannot view, update or delete another users catalog meal', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $meal = Meal::factory()->for($owner)->create();

    $this->actingAs($intruder)
        ->get(route('meals.edit', $meal))
        ->assertForbidden();

    $this->actingAs($intruder)
        ->put(route('meals.update', $meal), ['description' => 'Hijacked'])
        ->assertForbidden();

    $this->actingAs($intruder)
        ->delete(route('meals.destroy', $meal))
        ->assertForbidden();
});

test('the index page lists only the authenticated users catalog meals', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Meal::factory()->for($user)->create();
    Meal::factory()->for($user)->create();
    Meal::factory()->for($other)->create();

    $response = $this->actingAs($user)
        ->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => file_exists(public_path('build/manifest.json')) ? hash_file('xxh128', public_path('build/manifest.json')) : '',
        ])
        ->get(route('meals.index'))
        ->assertOk();

    expect($response->json('props.meals'))->toHaveCount(2);
});

test('a catalog meal can be created', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('meals.store'), [
        'description' => 'Chicken breast with rice and vegetables',
        'reference_weight_grams' => 350,
        'calories' => 500,
        'protein_grams' => 40.0,
    ]);

    $response->assertRedirect(route('meals.index'));

    $meal = $user->meals()->first();
    expect($meal)->not->toBeNull()
        ->and($meal->description)->toBe('Chicken breast with rice and vegetables')
        ->and($meal->reference_weight_grams)->toBe(350)
        ->and($meal->calories)->toBe(500)
        ->and((float) $meal->protein_grams)->toBe(40.0);
});

test('creating a catalog meal requires a description unique per user', function () {
    $user = User::factory()->create();
    Meal::factory()->for($user)->create(['description' => 'Greek yogurt with honey']);

    $this->actingAs($user)->post(route('meals.store'), [
        'description' => 'Greek yogurt with honey',
        'reference_weight_grams' => 150,
        'calories' => 180,
        'protein_grams' => 15.0,
    ])->assertSessionHasErrors('description');

    expect($user->meals()->count())->toBe(1);
});

test('two different users can each have a catalog meal with the same description', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    Meal::factory()->for($userA)->create(['description' => 'Greek yogurt with honey']);

    $this->actingAs($userB)->post(route('meals.store'), [
        'description' => 'Greek yogurt with honey',
        'reference_weight_grams' => 150,
        'calories' => 180,
        'protein_grams' => 15.0,
    ])->assertSessionHasNoErrors();

    expect($userB->meals()->count())->toBe(1);
});

test('a catalog meal can be created with a reference cost', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('meals.store'), [
        'description' => 'Chicken breast with rice and vegetables',
        'reference_weight_grams' => 350,
        'calories' => 500,
        'protein_grams' => 40.0,
        'reference_cost' => 8.5,
    ]);

    $response->assertRedirect(route('meals.index'));

    expect((float) $user->meals()->first()->reference_cost)->toBe(8.5);
});

test('a catalog meal reference cost is optional and defaults to null', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('meals.store'), [
        'description' => 'Chicken breast with rice and vegetables',
        'reference_weight_grams' => 350,
        'calories' => 500,
        'protein_grams' => 40.0,
    ])->assertSessionHasNoErrors();

    expect($user->meals()->first()->reference_cost)->toBeNull();
});

test('a catalog meal reference cost can be updated and cleared', function () {
    $user = User::factory()->create();
    $meal = Meal::factory()->for($user)->create(['reference_cost' => 5.0]);

    $this->actingAs($user)->put(route('meals.update', $meal), [
        'reference_cost' => 12.25,
    ])->assertSessionHasNoErrors();

    expect((float) $meal->fresh()->reference_cost)->toBe(12.25);

    $this->actingAs($user)->put(route('meals.update', $meal), [
        'reference_cost' => null,
    ])->assertSessionHasNoErrors();

    expect($meal->fresh()->reference_cost)->toBeNull();
});

test('a catalog meal can be updated by its owner', function () {
    $user = User::factory()->create();
    $meal = Meal::factory()->for($user)->create([
        'calories' => 200,
        'protein_grams' => 10.0,
    ]);

    $this->actingAs($user)->put(route('meals.update', $meal), [
        'calories' => 250,
        'protein_grams' => 12.5,
    ])->assertRedirect(route('meals.index'));

    expect($meal->fresh()->calories)->toBe(250)
        ->and((float) $meal->fresh()->protein_grams)->toBe(12.5);
});

test('a catalog meal can be deleted by its owner, and existing meal logs keep their values', function () {
    $user = User::factory()->create();
    $meal = Meal::factory()->for($user)->create();
    $mealLog = $user->mealLogs()->create([
        'meal_id' => $meal->id,
        'description' => $meal->description,
        'meal_type' => 'lunch',
        'weight_grams' => $meal->reference_weight_grams,
        'calories' => $meal->calories,
        'protein_grams' => $meal->protein_grams,
        'date' => '2026-05-01',
    ]);

    $this->actingAs($user)
        ->delete(route('meals.destroy', $meal))
        ->assertRedirect(route('meals.index'));

    expect(Meal::find($meal->id))->toBeNull();

    $mealLog->refresh();
    expect($mealLog->meal_id)->toBeNull()
        ->and($mealLog->calories)->toBe($meal->calories);
});
