<?php

declare(strict_types=1);

use App\Http\Controllers\MeasurementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('measurements', [MeasurementController::class, 'index'])->name('measurements.index');
    Route::get('measurements/create', [MeasurementController::class, 'create'])->name('measurements.create');
    Route::post('measurements', [MeasurementController::class, 'store'])->name('measurements.store');
    Route::get('measurements/{measurement}/summary', [MeasurementController::class, 'summary'])->name('measurements.summary');
    Route::get('measurements/{measurement}/edit', [MeasurementController::class, 'edit'])->name('measurements.edit');
    Route::put('measurements/{measurement}', [MeasurementController::class, 'update'])->name('measurements.update');
});
