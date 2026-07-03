<?php

declare(strict_types=1);

use App\Http\Controllers\MealLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('meal-logs', [MealLogController::class, 'index'])->name('meal-logs.index');
    Route::get('meal-logs/manage', [MealLogController::class, 'manage'])->name('meal-logs.manage');
    Route::get('meal-logs/create', [MealLogController::class, 'create'])->name('meal-logs.create');
    Route::post('meal-logs', [MealLogController::class, 'store'])->name('meal-logs.store');
    Route::get('meal-logs/{meal_log}/edit', [MealLogController::class, 'edit'])->name('meal-logs.edit');
    Route::put('meal-logs/{meal_log}', [MealLogController::class, 'update'])->name('meal-logs.update');
    Route::delete('meal-logs/{meal_log}', [MealLogController::class, 'destroy'])->name('meal-logs.destroy');
});
