<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Concerns\BuildsMeasurementChartSeries;
use App\Models\Measurement;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Base for the full-page "storico completo" chart pages: each one shows, expanded to fill the
 * page, the same single metric series shown (deferred, smaller) on the dashboard.
 */
abstract class MeasurementSeriesPageController extends Controller
{
    use BuildsMeasurementChartSeries;

    /**
     * The Inertia page component to render.
     */
    abstract protected function page(): string;

    /**
     * The Inertia prop key the series is passed under (matches the dashboard's naming).
     */
    abstract protected function propKey(): string;

    /**
     * @return Closure(Measurement): float
     */
    abstract protected function value(): Closure;

    public function __invoke(Request $request): Response
    {
        $measurements = $request->user()->measurements->sortBy('date')->values();

        return Inertia::render($this->page(), [
            $this->propKey() => $this->measurementSeries($measurements, $this->value()),
        ]);
    }
}
