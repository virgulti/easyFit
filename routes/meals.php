<?php

declare(strict_types=1);

use App\Http\Controllers\MealController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('meals', [MealController::class, 'index'])->name('meals.index');
    Route::get('meals/create', [MealController::class, 'create'])->name('meals.create');
    Route::post('meals', [MealController::class, 'store'])->name('meals.store');
    Route::get('meals/{meal}/edit', [MealController::class, 'edit'])->name('meals.edit');
    Route::put('meals/{meal}', [MealController::class, 'update'])->name('meals.update');
    Route::delete('meals/{meal}', [MealController::class, 'destroy'])->name('meals.destroy');
});
