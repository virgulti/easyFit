<?php

declare(strict_types=1);

use App\Models\Measurement;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

dataset('measurement_history_pages', [
    'weight' => ['weight-history', 'WeightHistory', 'weight_all'],
    'fat weight' => ['fat-weight-history', 'FatWeightHistory', 'fat_weight_all'],
    'muscle weight' => ['muscle-weight-history', 'MuscleWeightHistory', 'muscle_weight_all'],
    'bmi progress' => ['bmi-progress-history', 'BmiProgressHistory', 'bmi_progress_all'],
]);

test('guests are redirected to the login page', function (string $routeName) {
    $response = $this->get(route($routeName));
    $response->assertRedirect(route('login'));
})->with('measurement_history_pages');

test('authenticated users can visit the page', function (string $routeName) {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route($routeName));
    $response->assertOk();
})->with('measurement_history_pages');

test('the page exposes the full series for the authenticated user only', function (string $routeName, string $component, string $propKey) {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Measurement::factory()->for($user)->count(3)->create();
    Measurement::factory()->for($otherUser)->count(5)->create();

    $this->actingAs($user)
        ->get(route($routeName))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component($component)
            ->has("{$propKey}.labels", 3)
            ->has("{$propKey}.values", 3)
        );
})->with('measurement_history_pages');
