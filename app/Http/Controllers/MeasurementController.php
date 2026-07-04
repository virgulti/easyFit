<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreMeasurementRequest;
use App\Http\Requests\UpdateMeasurementRequest;
use App\Models\Measurement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MeasurementController extends Controller
{
    /**
     * Show the authenticated user's measurement history, newest first.
     */
    public function index(Request $request): Response
    {
        $measurements = $request->user()->measurements()
            ->orderByDesc('date')
            ->get();

        return Inertia::render('Measurements/Index', [
            'measurements' => $measurements,
        ]);
    }

    /**
     * Show the form for creating a new measurement.
     */
    public function create(): Response
    {
        return Inertia::render('Measurements/Create');
    }

    /**
     * Persist a new measurement for the authenticated user.
     */
    public function store(StoreMeasurementRequest $request): RedirectResponse
    {
        $measurement = $request->user()->measurements()->create($request->validated());

        return to_route('measurements.summary', $measurement);
    }

    /**
     * Show the summary for a single measurement, including derived metrics.
     */
    public function summary(Measurement $measurement): Response
    {
        Gate::authorize('view', $measurement);

        return Inertia::render('Measurements/Summary', [
            'measurement' => $measurement,
            'progress' => $measurement->progress,
            'bmiProgress' => $measurement->bmiProgress,
            'fatWeight' => $measurement->fatWeight,
            'muscleWeight' => $measurement->muscleWeight,
            'improvement' => $measurement->improvement(),
            'bmiBand' => $measurement->bmiBand(),
            'trends' => $measurement->trends(),
        ]);
    }

    /**
     * Show the form for editing an existing measurement.
     */
    public function edit(Measurement $measurement): Response
    {
        Gate::authorize('update', $measurement);

        return Inertia::render('Measurements/Edit', [
            'measurement' => $measurement,
        ]);
    }

    /**
     * Update an existing measurement belonging to the authenticated user.
     */
    public function update(UpdateMeasurementRequest $request, Measurement $measurement): RedirectResponse
    {
        $measurement->update($request->validated());

        return back(fallback: route('measurements.index'));
    }
}
