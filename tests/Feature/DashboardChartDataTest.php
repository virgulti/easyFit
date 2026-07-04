<?php

declare(strict_types=1);

use App\Models\Measurement;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

test('dashboard exposes the 8 plan chart series with the expected shape', function () {
    $user = User::factory()->create();

    Measurement::factory()->for($user)->create(['date' => Carbon::today()->subDays(2)]);
    Measurement::factory()->for($user)->create(['date' => Carbon::today()->subDays(1)]);
    Measurement::factory()->for($user)->create(['date' => Carbon::today()]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('progress_last_5_days.labels')
            ->has('progress_last_5_days.values')
            ->has('progress_last_5_weeks.labels')
            ->has('progress_last_5_weeks.values')
            ->has('progress_last_6_months.labels')
            ->has('progress_last_6_months.values')
            ->has('progress_last_1_year.labels')
            ->has('progress_last_1_year.values')
            ->where('fat_percentage_goal', null)
            // The *_all series are deferred (Inertia::defer): not present on the initial visit.
            ->missing('progress_all')
            ->missing('fat_percentage_all')
            ->missing('weight_all')
            ->missing('fat_weight_all')
            ->missing('muscle_weight_all')
            ->missing('bmi_progress_all')
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->has('progress_all.labels', 3)
                ->has('progress_all.values', 3)
                ->has('fat_percentage_all.labels', 3)
                ->has('fat_percentage_all.values', 3)
                ->has('weight_all.labels', 3)
                ->has('weight_all.values', 3)
                ->has('fat_weight_all.labels', 3)
                ->has('fat_weight_all.values', 3)
                ->has('muscle_weight_all.labels', 3)
                ->has('muscle_weight_all.values', 3)
                ->has('bmi_progress_all.labels', 3)
                ->has('bmi_progress_all.values', 3)
            )
        );
});

test('dashboard exposes the users fat percentage goal when set', function () {
    $user = User::factory()->create();
    $user->goal()->create(['max_fat_percentage' => 19.5]);

    Measurement::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('fat_percentage_goal', 19.5)
        );
});

test('progress last 5 days is anchored on the most recent measurement date with gaps left null', function () {
    $user = User::factory()->create();

    $today = Carbon::today();

    // Gaps on day-3 and day-1 relative to the most recent measurement (today).
    Measurement::factory()->for($user)->create(['date' => $today->copy()->subDays(4), 'weight' => 80]);
    Measurement::factory()->for($user)->create(['date' => $today->copy()->subDays(2), 'weight' => 78]);
    Measurement::factory()->for($user)->create(['date' => $today, 'weight' => 76]);

    $response = $this->actingAs($user)->get(route('dashboard'))->assertOk();

    $page = $response->viewData('page')['props'];

    expect($page['progress_last_5_days']['labels'])->toBe([
        $today->copy()->subDays(4)->toDateString(),
        $today->copy()->subDays(3)->toDateString(),
        $today->copy()->subDays(2)->toDateString(),
        $today->copy()->subDays(1)->toDateString(),
        $today->toDateString(),
    ]);

    $values = $page['progress_last_5_days']['values'];
    expect($values[1])->toBeNull()
        ->and($values[3])->toBeNull()
        ->and($values[0])->not->toBeNull()
        ->and($values[2])->not->toBeNull()
        ->and($values[4])->not->toBeNull();
});

test('dashboard loads the users measurements with a single query', function () {
    $user = User::factory()->create();
    Measurement::factory()->for($user)->count(10)->create();

    DB::enableQueryLog();
    $this->actingAs($user)->get(route('dashboard'))->assertOk();
    $measurementQueries = collect(DB::getQueryLog())
        ->filter(fn (array $entry): bool => str_contains($entry['query'], 'measurements'));
    DB::disableQueryLog();

    expect($measurementQueries)->toHaveCount(1);
});
