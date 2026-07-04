<?php

declare(strict_types=1);

use App\Models\Measurement;
use App\Models\User;

/**
 * Headers for an Inertia XHR visit: bypasses the full HTML/Vite render pipeline (which would
 * require the Vue page component to already be built) and returns the raw JSON page payload.
 * The version must match the middleware's computed asset version or it responds 409. The JSON
 * page payload (component/props) is asserted directly rather than via the `assertInertia()`
 * helper, which expects a full-page (non-XHR) response backed by a rendered Blade view.
 *
 * @return array<string, string>
 */
function inertiaHeaders(): array
{
    $manifest = public_path('build/manifest.json');

    return [
        'X-Inertia' => 'true',
        'X-Inertia-Version' => file_exists($manifest) ? hash_file('xxh128', $manifest) : '',
    ];
}

test('guests are redirected to login for measurement routes', function () {
    $measurement = Measurement::factory()->for(User::factory())->create();

    $this->get(route('measurements.index'))->assertRedirect(route('login'));
    $this->get(route('measurements.create'))->assertRedirect(route('login'));
    $this->get(route('measurements.summary', $measurement))->assertRedirect(route('login'));
    $this->get(route('measurements.edit', $measurement))->assertRedirect(route('login'));
});

test('store persists a measurement and redirects to its summary', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('measurements.store'), [
        'date' => '2026-07-01',
        'weight' => 80.5,
        'fat_perc' => 25.0,
        'muscle_perc' => 30.0,
        'bmi_value' => 24.5,
        'bedtime' => '23:00',
        'sleep_minutes' => 420,
        'notes' => 'felt good',
    ]);

    $measurement = Measurement::firstWhere('user_id', $user->id);

    expect($measurement)->not->toBeNull();
    $response->assertRedirect(route('measurements.summary', $measurement));

    $this->assertDatabaseHas('measurements', [
        'user_id' => $user->id,
        'weight' => 80.5,
    ]);
});

test('duplicate date for the same user is rejected', function () {
    $user = User::factory()->create();

    Measurement::factory()->for($user)->create(['date' => '2026-07-01']);

    $response = $this->actingAs($user)->post(route('measurements.store'), [
        'date' => '2026-07-01',
        'weight' => 80.5,
        'fat_perc' => 25.0,
        'muscle_perc' => 30.0,
        'bmi_value' => 24.5,
    ]);

    $response->assertSessionHasErrors('date');
    expect(Measurement::where('user_id', $user->id)->count())->toBe(1);
});

test('the same date is allowed for two different users', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    Measurement::factory()->for($userA)->create(['date' => '2026-07-01']);

    $response = $this->actingAs($userB)->post(route('measurements.store'), [
        'date' => '2026-07-01',
        'weight' => 80.5,
        'fat_perc' => 25.0,
        'muscle_perc' => 30.0,
        'bmi_value' => 24.5,
    ]);

    $response->assertSessionDoesntHaveErrors('date');
});

test('update persists changes for the owning user', function () {
    $user = User::factory()->create();
    $measurement = Measurement::factory()->for($user)->create(['weight' => 80.0]);

    $response = $this->actingAs($user)->put(route('measurements.update', $measurement), [
        'weight' => 78.5,
    ]);

    $response->assertRedirect();
    expect($measurement->fresh()->weight)->toEqual('78.5');
});

test('update allows setting only optional fields from the summary screen', function () {
    $user = User::factory()->create();
    $measurement = Measurement::factory()->for($user)->create(['notes' => null]);

    $this->actingAs($user)->put(route('measurements.update', $measurement), [
        'notes' => 'slept great',
    ])->assertSessionDoesntHaveErrors();

    expect($measurement->fresh()->notes)->toBe('slept great');
});

test('a user cannot view another users measurement summary', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $measurement = Measurement::factory()->for($owner)->create();

    $this->actingAs($intruder)
        ->get(route('measurements.summary', $measurement))
        ->assertForbidden();
});

test('a user cannot edit another users measurement', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $measurement = Measurement::factory()->for($owner)->create();

    $this->actingAs($intruder)
        ->get(route('measurements.edit', $measurement))
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

test('summary includes derived metrics', function () {
    $user = User::factory()->create();
    $measurement = Measurement::factory()->for($user)->create([
        'weight' => 80,
        'bmi_value' => 27,
    ]);

    // Requested as an Inertia XHR visit (X-Inertia header) so the response is the JSON page
    // payload rather than the full HTML shell, whose Vue component isn't built yet.
    $response = $this->actingAs($user)
        ->withHeaders(inertiaHeaders())
        ->get(route('measurements.summary', $measurement))
        ->assertOk();

    $page = $response->json();

    expect($page['component'])->toBe('Measurements/Summary')
        ->and($page['props']['measurement']['id'])->toBe($measurement->id)
        ->and($page['props']['progress'])->toBe(77.5)
        ->and($page['props']['bmiProgress'])->toBe(31.5)
        ->and($page['props'])->toHaveKeys(['fatWeight', 'muscleWeight', 'improvement', 'bmiBand', 'trends']);
});

test('index lists only the authenticated users measurements ordered by date desc', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Measurement::factory()->for($user)->create(['date' => '2026-06-01']);
    Measurement::factory()->for($user)->create(['date' => '2026-06-10']);
    Measurement::factory()->for($other)->create(['date' => '2026-06-15']);

    $response = $this->actingAs($user)
        ->withHeaders(inertiaHeaders())
        ->get(route('measurements.index'))
        ->assertOk();

    $page = $response->json();

    expect($page['component'])->toBe('Measurements/Index')
        ->and($page['props']['measurements'])->toHaveCount(2)
        ->and($page['props']['measurements'][0]['date'])->toContain('2026-06-10')
        ->and($page['props']['measurements'][1]['date'])->toContain('2026-06-01');
});
