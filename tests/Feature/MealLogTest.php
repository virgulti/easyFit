<?php

declare(strict_types=1);

use App\Models\Goal;
use App\Models\Meal;
use App\Models\MealLog;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('a user cannot view, update or delete another users meal logs', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $mealLog = MealLog::factory()->for($owner)->create();

    $this->actingAs($intruder)
        ->get(route('meal-logs.edit', $mealLog))
        ->assertForbidden();

    $this->actingAs($intruder)
        ->delete(route('meal-logs.destroy', $mealLog))
        ->assertForbidden();
});

test('deleting a meal log redirects to the day index, not back to its now-missing edit page', function () {
    $user = User::factory()->create();
    $mealLog = MealLog::factory()->for($user)->create(['date' => '2026-05-01']);

    $this->actingAs($user)
        ->delete(route('meal-logs.destroy', $mealLog))
        ->assertRedirect(route('meal-logs.index', ['date' => '2026-05-01']));

    expect(MealLog::find($mealLog->id))->toBeNull();
});

test('the manage page lists only the authenticated users meal logs, newest first', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    MealLog::factory()->for($user)->create(['date' => '2026-06-01']);
    MealLog::factory()->for($user)->create(['date' => '2026-06-10']);
    MealLog::factory()->for($other)->create(['date' => '2026-06-15']);

    $response = $this->actingAs($user)
        ->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => file_exists(public_path('build/manifest.json')) ? hash_file('xxh128', public_path('build/manifest.json')) : '',
        ])
        ->get(route('meal-logs.manage'))
        ->assertOk();

    $page = $response->json();

    expect($page['props']['mealLogs'])->toHaveCount(2)
        ->and($page['props']['mealLogs'][0]['date'])->toContain('2026-06-10')
        ->and($page['props']['mealLogs'][1]['date'])->toContain('2026-06-01');
});

test('a user cannot view another users meal logs in the index endpoint', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    MealLog::factory()->for($userA)->create(['date' => '2026-05-01']);
    MealLog::factory()->for($userB)->create(['date' => '2026-05-01']);

    $response = $this->actingAs($userA)
        ->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => file_exists(public_path('build/manifest.json')) ? hash_file('xxh128', public_path('build/manifest.json')) : '',
        ])
        ->get(route('meal-logs.index', ['date' => '2026-05-01']))
        ->assertOk();

    $page = $response->json();

    expect($page['props']['mealLogs'])->toHaveCount(1)
        ->and($page['props']['mealLogs'][0]['user_id'])->toBe($userA->id);
});

test('registering a meal from the catalog with unchanged weight uses the meal\'s calories and protein', function () {
    $user = User::factory()->create();
    $meal = Meal::factory()->for($user)->create([
        'reference_weight_grams' => 100,
        'calories' => 200,
        'protein_grams' => 10.5,
    ]);

    $response = $this->actingAs($user)->post(route('meal-logs.store'), [
        'meal_id' => $meal->id,
        'weight_grams' => 100,
        'meal_type' => 'lunch',
        'date' => '2026-05-01',
    ]);

    $response->assertRedirect();

    $mealLog = $user->mealLogs()->first();
    expect($mealLog->calories)->toBe(200)
        ->and((float) $mealLog->protein_grams)->toBe(10.5);
});

test('registering a meal from the catalog with changed weight scales calories and protein proportionally', function () {
    $user = User::factory()->create();
    $meal = Meal::factory()->for($user)->create([
        'reference_weight_grams' => 100,
        'calories' => 200,
        'protein_grams' => 10.5,
    ]);

    // Send different values to ensure they are ignored
    $response = $this->actingAs($user)->post(route('meal-logs.store'), [
        'meal_id' => $meal->id,
        'weight_grams' => 200, // double the reference weight
        'calories' => 999, // should be ignored
        'protein_grams' => 88.8, // should be ignored
        'meal_type' => 'lunch',
        'date' => '2026-05-01',
    ]);

    $response->assertRedirect();

    $mealLog = $user->mealLogs()->first();
    expect($mealLog->calories)->toBe(400) // 200 * 2
        ->and((float) $mealLog->protein_grams)->toBe(21.0); // 10.5 * 2
});

test('registering a meal from the catalog with a reference cost scales it proportionally, ignoring client input', function () {
    $user = User::factory()->create();
    $meal = Meal::factory()->for($user)->create([
        'reference_weight_grams' => 100,
        'reference_cost' => 4.0,
    ]);

    $response = $this->actingAs($user)->post(route('meal-logs.store'), [
        'meal_id' => $meal->id,
        'weight_grams' => 250, // 2.5x the reference weight
        'cost' => 999, // should be ignored
        'meal_type' => 'lunch',
        'date' => '2026-05-01',
    ]);

    $response->assertRedirect();

    expect((float) $user->mealLogs()->first()->cost)->toBe(10.0); // 4.0 * 2.5
});

test('registering a meal from the catalog without a reference cost trusts the client-supplied cost', function () {
    $user = User::factory()->create();
    $meal = Meal::factory()->for($user)->create(['reference_cost' => null]);

    $this->actingAs($user)->post(route('meal-logs.store'), [
        'meal_id' => $meal->id,
        'weight_grams' => $meal->reference_weight_grams,
        'cost' => 6.5,
        'meal_type' => 'lunch',
        'date' => '2026-05-01',
    ])->assertRedirect();

    expect((float) $user->mealLogs()->first()->cost)->toBe(6.5);
});

test('updating the weight of a catalog meal log with a reference cost rescales the stored cost', function () {
    $user = User::factory()->create();
    $meal = Meal::factory()->for($user)->create([
        'reference_weight_grams' => 100,
        'reference_cost' => 4.0,
    ]);
    $mealLog = $user->mealLogs()->create([
        'meal_id' => $meal->id,
        'description' => $meal->description,
        'meal_type' => 'lunch',
        'weight_grams' => 100,
        'calories' => $meal->calories,
        'protein_grams' => $meal->protein_grams,
        'cost' => 4.0,
        'date' => '2026-05-01',
    ]);

    $this->actingAs($user)->put(route('meal-logs.update', $mealLog), [
        'weight_grams' => 200,
    ])->assertSessionHasNoErrors();

    expect((float) $mealLog->fresh()->cost)->toBe(8.0); // 4.0 * 2
});

test('registering an unusual meal without save_to_catalog does not create a catalog entry', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('meal-logs.store'), [
        'description' => 'Pasta with tomato sauce',
        'meal_type' => 'lunch',
        'weight_grams' => 150,
        'calories' => 300,
        'protein_grams' => 12.0,
        'date' => '2026-05-01',
    ]);

    $response->assertRedirect();

    expect($user->mealLogs()->count())->toBe(1)
        ->and($user->meals()->count())->toBe(0);
});

test('registering an unusual meal with save_to_catalog creates both a log and a catalog entry', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('meal-logs.store'), [
        'description' => 'Pasta with tomato sauce',
        'meal_type' => 'lunch',
        'weight_grams' => 150,
        'calories' => 300,
        'protein_grams' => 12.0,
        'date' => '2026-05-01',
        'save_to_catalog' => true,
    ]);

    $response->assertRedirect();

    expect($user->mealLogs()->count())->toBe(1)
        ->and($user->meals()->count())->toBe(1);

    $mealLog = $user->mealLogs()->first();
    $meal = $user->meals()->first();

    expect($mealLog->description)->toBe('Pasta with tomato sauce')
        ->and($mealLog->calories)->toBe(300)
        ->and((float) $mealLog->protein_grams)->toBe(12.0);

    expect($meal->description)->toBe('Pasta with tomato sauce')
        ->and($meal->reference_weight_grams)->toBe(150)
        ->and($meal->calories)->toBe(300)
        ->and((float) $meal->protein_grams)->toBe(12.0);
});

test('multiple meal logs can exist for the same user on the same day', function () {
    $user = User::factory()->create();

    MealLog::factory()->for($user)->create([
        'date' => '2026-05-01',
        'calories' => 200,
        'protein_grams' => 10.0,
    ]);
    MealLog::factory()->for($user)->create([
        'date' => '2026-05-01',
        'calories' => 300,
        'protein_grams' => 15.0,
    ]);
    MealLog::factory()->for($user)->create([
        'date' => '2026-05-01',
        'calories' => 400,
        'protein_grams' => 20.0,
    ]);

    $response = $this->actingAs($user)
        ->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => file_exists(public_path('build/manifest.json')) ? hash_file('xxh128', public_path('build/manifest.json')) : '',
        ])
        ->get(route('meal-logs.index', ['date' => '2026-05-01']))
        ->assertOk();

    $page = $response->json();

    expect($page['props']['mealLogs'])->toHaveCount(3)
        ->and($page['props']['totals']['calories'])->toBe(900)
        ->and($page['props']['totals']['protein_grams'])->toEqual(45.0);
});

test('meal log totals are correctly calculated for a single day', function () {
    $user = User::factory()->create();

    // Create meal logs for different days and users
    MealLog::factory()->for($user)->create([
        'date' => '2026-05-01',
        'calories' => 200,
        'protein_grams' => 10.0,
    ]);
    MealLog::factory()->for($user)->create([
        'date' => '2026-05-02',
        'calories' => 300,
        'protein_grams' => 15.0,
    ]);
    MealLog::factory()->for(User::factory()->create())->create([
        'date' => '2026-05-01',
        'calories' => 400,
        'protein_grams' => 20.0,
    ]);

    $response = $this->actingAs($user)
        ->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => file_exists(public_path('build/manifest.json')) ? hash_file('xxh128', public_path('build/manifest.json')) : '',
        ])
        ->get(route('meal-logs.index', ['date' => '2026-05-01']))
        ->assertOk();

    $page = $response->json();

    expect($page['props']['mealLogs'])->toHaveCount(1)
        ->and($page['props']['totals']['calories'])->toBe(200)
        ->and($page['props']['totals']['protein_grams'])->toEqual(10.0);
});

test('thresholds are null when the user has not set a goal', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('meal-logs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('thresholds.min_protein_grams', null)
            ->where('thresholds.max_calories_per_day', null)
        );
});

test('thresholds reflect the users configured goal', function () {
    $user = User::factory()->create();
    Goal::factory()->for($user)->create([
        'min_protein_grams' => 160,
        'max_calories_per_day' => 1500,
    ]);

    $this->actingAs($user)
        ->get(route('meal-logs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('thresholds.min_protein_grams', 160)
            ->where('thresholds.max_calories_per_day', 1500)
        );
});

test('a cost can be recorded when registering a meal from the catalog', function () {
    $user = User::factory()->create();
    $meal = Meal::factory()->for($user)->create();

    $this->actingAs($user)->post(route('meal-logs.store'), [
        'meal_id' => $meal->id,
        'weight_grams' => $meal->reference_weight_grams,
        'meal_type' => 'lunch',
        'date' => '2026-05-01',
        'cost' => 8.5,
    ])->assertRedirect();

    expect((float) $user->mealLogs()->first()->cost)->toBe(8.5);
});

test('cost is optional and defaults to null', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('meal-logs.store'), [
        'description' => 'Pasta with tomato sauce',
        'meal_type' => 'lunch',
        'weight_grams' => 150,
        'calories' => 300,
        'protein_grams' => 12.0,
        'date' => '2026-05-01',
    ])->assertRedirect();

    expect($user->mealLogs()->first()->cost)->toBeNull();
});

test('cost can be updated and cleared', function () {
    $user = User::factory()->create();
    $mealLog = MealLog::factory()->for($user)->create(['cost' => 5.0]);

    $this->actingAs($user)->put(route('meal-logs.update', $mealLog), [
        'cost' => 12.25,
    ])->assertSessionHasNoErrors();

    expect((float) $mealLog->fresh()->cost)->toBe(12.25);

    $this->actingAs($user)->put(route('meal-logs.update', $mealLog), [
        'cost' => null,
    ])->assertSessionHasNoErrors();

    expect($mealLog->fresh()->cost)->toBeNull();
});

test('the daily total cost sums only entries with a recorded cost', function () {
    $user = User::factory()->create();
    MealLog::factory()->for($user)->create(['date' => '2026-05-01', 'cost' => 8.5]);
    MealLog::factory()->for($user)->create(['date' => '2026-05-01', 'cost' => 4.25]);
    MealLog::factory()->for($user)->create(['date' => '2026-05-01', 'cost' => null]);

    $this->actingAs($user)
        ->get(route('meal-logs.index', ['date' => '2026-05-01']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('totals.cost', 12.75)
        );
});

test('the daily total cost is null when no meal that day has a recorded cost', function () {
    $user = User::factory()->create();
    MealLog::factory()->for($user)->create(['date' => '2026-05-01', 'cost' => null]);

    $this->actingAs($user)
        ->get(route('meal-logs.index', ['date' => '2026-05-01']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('totals.cost', null));
});
