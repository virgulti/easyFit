<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreMealRequest;
use App\Http\Requests\UpdateMealRequest;
use App\Models\Meal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MealController extends Controller
{
    /**
     * Show the authenticated user's meal catalog.
     */
    public function index(Request $request): Response
    {
        $meals = $request->user()->meals()
            ->orderBy('meal_type')
            ->orderBy('description')
            ->get();

        return Inertia::render('Meals/Index', [
            'meals' => $meals,
        ]);
    }

    /**
     * Show the form for adding a new catalog meal.
     */
    public function create(): Response
    {
        return Inertia::render('Meals/Create');
    }

    /**
     * Persist a new catalog meal for the authenticated user.
     */
    public function store(StoreMealRequest $request): RedirectResponse
    {
        $request->user()->meals()->create($request->validated());

        return to_route('meals.index');
    }

    /**
     * Show the form for editing a catalog meal.
     */
    public function edit(Meal $meal): Response
    {
        Gate::authorize('update', $meal);

        return Inertia::render('Meals/Edit', [
            'meal' => $meal,
        ]);
    }

    /**
     * Update an existing catalog meal belonging to the authenticated user.
     */
    public function update(UpdateMealRequest $request, Meal $meal): RedirectResponse
    {
        $meal->update($request->validated());

        return to_route('meals.index');
    }

    /**
     * Delete a catalog meal belonging to the authenticated user. Meal logs previously created
     * from it keep their own copied values and simply lose the catalog reference, since the
     * foreign key is nullOnDelete.
     */
    public function destroy(Meal $meal): RedirectResponse
    {
        Gate::authorize('delete', $meal);

        $meal->delete();

        return to_route('meals.index');
    }
}
