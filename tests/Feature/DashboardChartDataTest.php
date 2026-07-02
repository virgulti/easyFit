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
            // The *_all series are deferred (Inertia::defer): not present on the initial visit.
            ->missing('progress_all')
            ->missing('weight_all')
            ->missing('fat_weight_all')
            ->missing('muscle_weight_all')
            ->missing('bmi_progress_all')
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->has('progress_all.labels', 3)
                ->has('progress_all.values', 3)
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
