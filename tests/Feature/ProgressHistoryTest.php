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
