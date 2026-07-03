<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Measurement;
use Closure;

class FatWeightHistoryController extends MeasurementSeriesPageController
{
    protected function page(): string
    {
        return 'FatWeightHistory';
    }

    protected function propKey(): string
    {
        return 'fat_weight_all';
    }

    protected function value(): Closure
    {
        return fn (Measurement $measurement): float => $measurement->fatWeight;
    }
}
