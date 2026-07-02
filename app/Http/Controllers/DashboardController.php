<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Measurement;
use App\Services\WeeklyAverageService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the dashboard with the chart data series described in PLAN.md
     * (#schermata principale): progress over the last 5 days/weeks/6 months, plus the full
     * history of progress, weight, fat/muscle weight and BMI progress.
     */
    public function __invoke(Request $request, WeeklyAverageService $weeklyAverageService): Response
    {
        $user = $request->user();

        // Single query for all of the user's measurements; both the per-measurement series below
        // and WeeklyAverageService (which reads $user->measurements internally) reuse this
        // already-loaded relation instead of querying again.
        $user->load('measurements');

        $measurements = $user->measurements->sortBy('date')->values();

        $weeks = $weeklyAverageService->averagesForUser($user);
        $sixMonthsAgo = Carbon::today()->subMonths(6)->toDateString();
        $weeksInLast6Months = array_values(array_filter(
            $weeks,
            fn (array $week): bool => $week['week_start'] >= $sixMonthsAgo,
        ));

        return Inertia::render('Dashboard', [
            'progress_last_5_days' => $this->measurementSeries(
                $measurements->slice(-5)->values(),
                fn (Measurement $measurement): float => $measurement->progress,
            ),
            'progress_last_5_weeks' => $this->weeklySeries(array_slice($weeks, -5), 'progress'),
            'progress_last_6_months' => $this->weeklySeries($weeksInLast6Months, 'progress'),
            'progress_all' => Inertia::defer(
                fn () => $this->measurementSeries($measurements, fn (Measurement $measurement): float => $measurement->progress),
                'history',
            ),
            'weight_all' => Inertia::defer(
                fn () => $this->measurementSeries($measurements, fn (Measurement $measurement): float => (float) $measurement->weight),
                'history',
            ),
            'fat_weight_all' => Inertia::defer(
                fn () => $this->measurementSeries($measurements, fn (Measurement $measurement): float => $measurement->fatWeight),
                'history',
            ),
            'muscle_weight_all' => Inertia::defer(
                fn () => $this->measurementSeries($measurements, fn (Measurement $measurement): float => $measurement->muscleWeight),
                'history',
            ),
            'bmi_progress_all' => Inertia::defer(
                fn () => $this->measurementSeries($measurements, fn (Measurement $measurement): float => $measurement->bmiProgress),
                'history',
            ),
        ]);
    }

    /**
     * Build a chart series (labels/values) from a collection of measurements.
     *
     * @param  Collection<int, Measurement>  $measurements
     * @param  Closure(Measurement): float  $value
     * @return array{labels: array<int, string>, values: array<int, float>}
     */
    private function measurementSeries(Collection $measurements, Closure $value): array
    {
        return [
            'labels' => $measurements->map(fn (Measurement $measurement): string => $measurement->date->toDateString())->values()->all(),
            'values' => $measurements->map($value)->values()->all(),
        ];
    }

    /**
     * Build a chart series (labels/values) from WeeklyAverageService weeks.
     *
     * @param  array<int, array<string, mixed>>  $weeks
     * @return array{labels: array<int, string>, values: array<int, float>}
     */
    private function weeklySeries(array $weeks, string $valueKey): array
    {
        return [
            'labels' => array_column($weeks, 'week_start'),
            'values' => array_column($weeks, $valueKey),
        ];
    }
}
