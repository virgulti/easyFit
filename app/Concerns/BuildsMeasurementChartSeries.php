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
}
