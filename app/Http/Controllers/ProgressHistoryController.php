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

/**
 * Full-page progress chart with a time-window switcher (5 days / 5 weeks / 6 months / 1 year /
 * all), mirroring the windows already computed for the dashboard's progress charts.
 */
class ProgressHistoryController extends Controller
{
    use BuildsMeasurementChartSeries;

    public function __invoke(Request $request, WeeklyAverageService $weeklyAverageService): Response
    {
        $user = $request->user();
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

        return Inertia::render('ProgressHistory', [
            'progress_last_5_days' => $this->lastDaysSeries(
                $measurements,
                fn (Measurement $measurement): float => $measurement->progress,
            ),
            'progress_last_5_weeks' => $this->weeklySeries(array_slice($weeks, -5), 'progress'),
            'progress_last_6_months' => $this->weeklySeries($weeksInLast6Months, 'progress'),
            'progress_last_1_year' => $this->weeklySeries($weeksInLastYear, 'progress'),
            'progress_all' => $this->measurementSeries($measurements, fn (Measurement $measurement): float => $measurement->progress),
        ]);
    }
}
