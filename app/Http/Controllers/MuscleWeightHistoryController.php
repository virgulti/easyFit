<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Measurement;
use Closure;

class MuscleWeightHistoryController extends MeasurementSeriesPageController
{
    protected function page(): string
    {
        return 'MuscleWeightHistory';
    }

    protected function propKey(): string
    {
        return 'muscle_weight_all';
    }

    protected function value(): Closure
    {
        return fn (Measurement $measurement): float => $measurement->muscleWeight;
    }
}
