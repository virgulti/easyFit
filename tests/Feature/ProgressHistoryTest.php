<?php

declare(strict_types=1);

use App\Models\Measurement;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('progress-history'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the progress history page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('progress-history'));
    $response->assertOk();
});

test('progress history exposes the full progress series for the authenticated user only', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Measurement::factory()->for($user)->count(3)->create();
    Measurement::factory()->for($otherUser)->count(5)->create();

    $this->actingAs($user)
        ->get(route('progress-history'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('ProgressHistory')
            ->has('progress_all.labels', 3)
            ->has('progress_all.values', 3)
        );
});

test('progress history exposes every time-window series', function () {
    $user = User::factory()->create();

    Measurement::factory()->for($user)->count(3)->create();

    $this->actingAs($user)
        ->get(route('progress-history'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('ProgressHistory')
            ->has('progress_last_5_days.labels')
            ->has('progress_last_5_days.values')
            ->has('progress_last_5_weeks.labels')
            ->has('progress_last_5_weeks.values')
            ->has('progress_last_6_months.labels')
            ->has('progress_last_6_months.values')
            ->has('progress_last_1_year.labels')
            ->has('progress_last_1_year.values')
        );
});
