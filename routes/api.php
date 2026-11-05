<?php

use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\ScheduleController;
use App\Http\Controllers\Api\V1\ScheduleShiftController;
use App\Http\Controllers\Api\V1\ShiftTemplateController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('shift-templates', [ShiftTemplateController::class, 'index'])->name('shift-templates.index');

    Route::apiResource('schedules', ScheduleController::class)->only(['index', 'store', 'show']);
    Route::post('schedules/{schedule}/save', [ScheduleController::class, 'save'])->name('schedules.save');
    Route::post('schedules/{schedule}/publish', [ScheduleController::class, 'publish'])->name('schedules.publish');

    Route::prefix('schedules/{schedule}')->name('schedules.')->group(function () {
        Route::post('shifts/bulk', [ScheduleShiftController::class, 'bulkStore'])->name('shifts.bulk-store');
        Route::delete('shifts/bulk', [ScheduleShiftController::class, 'bulkDestroy'])->name('shifts.bulk-destroy');
        Route::apiResource('shifts', ScheduleShiftController::class)->except(['show'])->scoped();
    });
});
