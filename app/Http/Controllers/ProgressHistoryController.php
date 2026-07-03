<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Measurement;
use Closure;

class ProgressHistoryController extends MeasurementSeriesPageController
{
    protected function page(): string
    {
        return 'ProgressHistory';
    }

    protected function propKey(): string
    {
        return 'progress_all';
    }

    protected function value(): Closure
    {
        return fn (Measurement $measurement): float => $measurement->progress;
    }
}
