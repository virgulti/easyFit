<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Measurement;
use Closure;

class BmiProgressHistoryController extends MeasurementSeriesPageController
{
    protected function page(): string
    {
        return 'BmiProgressHistory';
    }

    protected function propKey(): string
    {
        return 'bmi_progress_all';
    }

    protected function value(): Closure
    {
        return fn (Measurement $measurement): float => $measurement->bmiProgress;
    }
}
