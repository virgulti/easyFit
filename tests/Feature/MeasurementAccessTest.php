<?php

declare(strict_types=1);

use App\Models\Measurement;
use App\Models\User;

test('a user cannot view another users measurement summary', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $measurement = Measurement::factory()->for($owner)->create();

    $this->actingAs($intruder)
        ->get(route('measurements.summary', $measurement))
        ->assertForbidden();
});

test('a user cannot update another users measurement', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $measurement = Measurement::factory()->for($owner)->create(['weight' => 80.0]);

    $this->actingAs($intruder)
        ->put(route('measurements.update', $measurement), ['weight' => 60.0])
        ->assertForbidden();

    expect($measurement->fresh()->weight)->toEqual('80.0');
});

test('the same date is allowed for two different users', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    Measurement::factory()->for($userA)->create(['date' => '2026-05-01']);

    $response = $this->actingAs($userB)->post(route('measurements.store'), [
        'date' => '2026-05-01',
        'weight' => 80.5,
        'fat_perc' => 25.0,
        'muscle_perc' => 30.0,
        'bmi_value' => 24.5,
    ]);

    $response->assertSessionDoesntHaveErrors('date');
});

test('duplicate date for the same user is rejected', function () {
    $user = User::factory()->create();

    Measurement::factory()->for($user)->create(['date' => '2026-05-01']);

    $response = $this->actingAs($user)->post(route('measurements.store'), [
        'date' => '2026-05-01',
        'weight' => 80.5,
        'fat_perc' => 25.0,
        'muscle_perc' => 30.0,
        'bmi_value' => 24.5,
    ]);

    $response->assertSessionHasErrors('date');
});

test('index lists only the authenticated users measurements ordered by date desc', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Measurement::factory()->for($user)->create(['date' => '2026-06-01']);
    Measurement::factory()->for($user)->create(['date' => '2026-06-10']);
    Measurement::factory()->for($other)->create(['date' => '2026-06-15']);

    $response = $this->actingAs($user)
        ->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => file_exists(public_path('build/manifest.json')) ? hash_file('xxh128', public_path('build/manifest.json')) : '',
        ])
        ->get(route('measurements.index'))
        ->assertOk();

    $page = $response->json();

    expect($page['props']['measurements'])->toHaveCount(2)
        ->and($page['props']['measurements'][0]['date'])->toContain('2026-06-10')
        ->and($page['props']['measurements'][1]['date'])->toContain('2026-06-01');
});
