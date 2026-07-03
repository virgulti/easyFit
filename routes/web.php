<?php

use App\Http\Controllers\BmiProgressHistoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FatWeightHistoryController;
use App\Http\Controllers\MuscleWeightHistoryController;
use App\Http\Controllers\ProgressHistoryController;
use App\Http\Controllers\WeightHistoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'))->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('progress-storico', ProgressHistoryController::class)->name('progress-history');
    Route::get('peso-storico', WeightHistoryController::class)->name('weight-history');
    Route::get('massa-grassa-storico', FatWeightHistoryController::class)->name('fat-weight-history');
    Route::get('massa-muscolare-storico', MuscleWeightHistoryController::class)->name('muscle-weight-history');
    Route::get('bmi-progress-storico', BmiProgressHistoryController::class)->name('bmi-progress-history');
    Route::inertia('bmi-guide', 'BmiGuide')->name('bmi-guide');
});

require __DIR__.'/settings.php';
require __DIR__.'/measurements.php';
require __DIR__.'/meal_logs.php';
