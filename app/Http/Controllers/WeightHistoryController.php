<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Measurement;
use Closure;

class WeightHistoryController extends MeasurementSeriesPageController
{
    protected function page(): string
    {
        return 'WeightHistory';
    }

    protected function propKey(): string
    {
        return 'weight_all';
    }

    protected function value(): Closure
    {
        return fn (Measurement $measurement): float => (float) $measurement->weight;
    }
}
