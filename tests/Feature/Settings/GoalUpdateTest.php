<?php

declare(strict_types=1);

use App\Models\Goal;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get(route('goals.edit'))->assertRedirect(route('login'));
});

test('goals page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('goals.edit'))->assertOk();
});

test('goals can be set for the first time', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put(route('goals.update'), [
        'max_fat_percentage' => 19.0,
        'min_protein_grams' => 160,
        'max_calories_per_day' => 1500,
        'max_calories_per_week' => 10500,
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('goals.edit'));

    $goal = $user->goal()->firstOrFail();
    expect((float) $goal->max_fat_percentage)->toBe(19.0)
        ->and($goal->min_protein_grams)->toBe(160)
        ->and($goal->max_calories_per_day)->toBe(1500)
        ->and($goal->max_calories_per_week)->toBe(10500);
});

test('goals can be updated rather than duplicated', function () {
    $user = User::factory()->create();
    Goal::factory()->for($user)->create(['min_protein_grams' => 100]);

    $this->actingAs($user)->put(route('goals.update'), [
        'min_protein_grams' => 180,
    ])->assertSessionHasNoErrors();

    expect(Goal::where('user_id', $user->id)->count())->toBe(1)
        ->and($user->goal()->firstOrFail()->min_protein_grams)->toBe(180);
});

test('a threshold can be cleared by sending it as null', function () {
    $user = User::factory()->create();
    Goal::factory()->for($user)->create(['min_protein_grams' => 160]);

    $this->actingAs($user)->put(route('goals.update'), [
        'min_protein_grams' => null,
    ])->assertSessionHasNoErrors();

    expect($user->goal()->firstOrFail()->min_protein_grams)->toBeNull();
});

test('a user only sees and updates their own goal', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    Goal::factory()->for($userB)->create(['min_protein_grams' => 200]);

    $this->actingAs($userA)->put(route('goals.update'), [
        'min_protein_grams' => 100,
    ])->assertSessionHasNoErrors();

    expect($userA->goal()->firstOrFail()->min_protein_grams)->toBe(100)
        ->and($userB->goal()->firstOrFail()->min_protein_grams)->toBe(200);
});
