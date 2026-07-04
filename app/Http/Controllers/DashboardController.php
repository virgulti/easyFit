<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Concerns\BuildsMeasurementChartSeries;
use App\Models\Measurement;
use App\Services\WeeklyAverageService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    use BuildsMeasurementChartSeries;

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
        $oneYearAgo = Carbon::today()->subYear()->toDateString();
        $weeksInLastYear = array_values(array_filter(
            $weeks,
            fn (array $week): bool => $week['week_start'] >= $oneYearAgo,
        ));

        $goal = $user->goal;

        return Inertia::render('Dashboard', [
            'progress_last_5_days' => $this->lastDaysSeries(
                $measurements,
                fn (Measurement $measurement): float => $measurement->progress,
            ),
            'progress_last_5_weeks' => $this->weeklySeries(array_slice($weeks, -5), 'progress'),
            'progress_last_6_months' => $this->weeklySeries($weeksInLast6Months, 'progress'),
            'progress_last_1_year' => $this->weeklySeries($weeksInLastYear, 'progress'),
            'progress_all' => Inertia::defer(
                fn () => $this->measurementSeries($measurements, fn (Measurement $measurement): float => $measurement->progress),
                'history',
            ),
            'fat_percentage_all' => Inertia::defer(
                fn () => $this->measurementSeries($measurements, fn (Measurement $measurement): float => (float) $measurement->fat_perc),
                'history',
            ),
            'fat_percentage_goal' => $goal?->max_fat_percentage !== null ? (float) $goal->max_fat_percentage : null,
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
}
