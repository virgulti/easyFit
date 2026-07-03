<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreMealLogRequest;
use App\Http\Requests\UpdateMealLogRequest;
use App\Models\Meal;
use App\Models\MealLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MealLogController extends Controller
{
    /**
     * Show the authenticated user's registered meals for a single day (default today), with
     * the day's total calories and protein.
     */
    public function index(Request $request): Response
    {
        $date = $request->query('date')
            ? Carbon::parse((string) $request->query('date'))
            : Carbon::today();

        $mealLogs = $request->user()->mealLogs()
            ->whereDate('date', $date)
            ->orderBy('created_at')
            ->get();

        return Inertia::render('MealLogs/Index', [
            'date' => $date->toDateString(),
            'mealLogs' => $mealLogs,
            'totals' => [
                'calories' => (int) $mealLogs->sum('calories'),
                'protein_grams' => round((float) $mealLogs->sum('protein_grams'), 1),
            ],
        ]);
    }

    /**
     * Show the form for registering a meal, either from the catalog or as a one-off "pasto
     * inusuale".
     */
    public function create(Request $request): Response
    {
        $meals = $request->user()->meals()->orderBy('description')->get();

        $date = $request->query('date')
            ? Carbon::parse((string) $request->query('date'))->toDateString()
            : Carbon::today()->toDateString();

        return Inertia::render('MealLogs/Create', [
            'meals' => $meals,
            'date' => $date,
        ]);
    }

    /**
     * Persist a new registered meal for the authenticated user.
     */
    public function store(StoreMealLogRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();

        if (! empty($data['meal_id'])) {
            /** @var Meal $meal */
            $meal = $user->meals()->findOrFail($data['meal_id']);
            $scaled = $meal->scaledTo($data['weight_grams']);

            $mealLog = $user->mealLogs()->create([
                'meal_id' => $meal->id,
                'description' => $meal->description,
                'meal_type' => $data['meal_type'],
                'weight_grams' => $data['weight_grams'],
                'calories' => $scaled['calories'],
                'protein_grams' => $scaled['protein_grams'],
                'date' => $data['date'],
            ]);
        } else {
            $mealLog = $user->mealLogs()->create([
                'meal_id' => null,
                'description' => $data['description'],
                'meal_type' => $data['meal_type'],
                'weight_grams' => $data['weight_grams'],
                'calories' => $data['calories'],
                'protein_grams' => $data['protein_grams'],
                'date' => $data['date'],
            ]);

            if ($request->boolean('save_to_catalog')) {
                $user->meals()->create([
                    'description' => $data['description'],
                    'meal_type' => $data['meal_type'],
                    'reference_weight_grams' => $data['weight_grams'],
                    'calories' => $data['calories'],
                    'protein_grams' => $data['protein_grams'],
                ]);
            }
        }

        return to_route('meal-logs.index', ['date' => $mealLog->date->toDateString()]);
    }

    /**
     * Show the form for editing a registered meal.
     */
    public function edit(MealLog $mealLog): Response
    {
        Gate::authorize('update', $mealLog);

        return Inertia::render('MealLogs/Edit', [
            'mealLog' => $mealLog,
            'meal' => $mealLog->meal,
        ]);
    }

    /**
     * Update an existing registered meal belonging to the authenticated user. If it came from
     * the catalog and the weight changed, calories/protein are recomputed from the catalog meal
     * rather than trusting client-supplied values.
     */
    public function update(UpdateMealLogRequest $request, MealLog $mealLog): RedirectResponse
    {
        $data = $request->validated();

        if ($mealLog->meal_id !== null && array_key_exists('weight_grams', $data)) {
            $scaled = $mealLog->meal->scaledTo($data['weight_grams']);
            $data['calories'] = $scaled['calories'];
            $data['protein_grams'] = $scaled['protein_grams'];
        }

        $mealLog->update($data);

        return back(fallback: route('meal-logs.index', ['date' => $mealLog->date->toDateString()]));
    }

    /**
     * Delete a registered meal belonging to the authenticated user.
     */
    public function destroy(MealLog $mealLog): RedirectResponse
    {
        Gate::authorize('delete', $mealLog);

        $date = $mealLog->date->toDateString();
        $mealLog->delete();

        return to_route('meal-logs.index', ['date' => $date]);
    }
}
