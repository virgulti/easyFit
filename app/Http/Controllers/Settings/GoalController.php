<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateGoalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GoalController extends Controller
{
    /**
     * Show the user's goal (threshold) settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Goals', [
            'goal' => $request->user()->goal,
        ]);
    }

    /**
     * Update (or create) the user's goal thresholds.
     */
    public function update(UpdateGoalRequest $request): RedirectResponse
    {
        $request->user()->goal()->updateOrCreate([], $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Obiettivi aggiornati.')]);

        return to_route('goals.edit');
    }
}
