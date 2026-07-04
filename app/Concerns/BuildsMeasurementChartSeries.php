<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Models\Measurement;
use Closure;
use Illuminate\Support\Collection;

trait BuildsMeasurementChartSeries
{
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

    /**
     * Build a chart series covering the last $days calendar days, anchored on the most
     * recent measurement's date. Days without a measurement get a null value so the chart
     * shows a gap instead of silently compressing to whichever records happen to exist.
     *
     * @param  Collection<int, Measurement>  $measurements
     * @param  Closure(Measurement): float  $value
     * @return array{labels: array<int, string>, values: array<int, float|null>}
     */
    private function lastDaysSeries(Collection $measurements, Closure $value, int $days = 5): array
    {
        $latest = $measurements->last();

        if ($latest === null) {
            return ['labels' => [], 'values' => []];
        }

        $byDate = $measurements->keyBy(fn (Measurement $measurement): string => $measurement->date->toDateString());

        $labels = [];
        $values = [];

        for ($offset = $days - 1; $offset >= 0; $offset--) {
            $date = $latest->date->copy()->subDays($offset)->toDateString();
            $measurement = $byDate->get($date);

            $labels[] = $date;
            $values[] = $measurement === null ? null : $value($measurement);
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
