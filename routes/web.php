<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Protected Routes (require authentication)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/logs', [DashboardController::class, 'logs'])->name('logs');
    Route::get('/write-logs', [DashboardController::class, 'writeLogs'])->name('write-logs');
    Route::get('/preventive-maintenance', [DashboardController::class, 'preventiveMaintenance'])->name('preventive-maintenance');

    // Power control routes
    Route::post('/dashboard/toggle-power', [DashboardController::class, 'togglePower'])->name('dashboard.toggle-power');
    Route::post('/dashboard/power-status', [DashboardController::class, 'getPowerStatus'])->name('dashboard.power-status');

    // Maintenance status routes
    Route::post('/dashboard/maintenance-status', [DashboardController::class, 'updateMaintenanceStatus'])->name('dashboard.maintenance-status');
    Route::get('/dashboard/maintenance-status', [DashboardController::class, 'getMaintenanceStatus'])->name('dashboard.get-maintenance-status');

    // Runtime details routes
    Route::get('/dashboard/runtime-details/{generatorId}', [DashboardController::class, 'getRuntimeDetails'])->name('dashboard.runtime-details');

    // Generator runtime table (hidden from navbar)
    Route::get('/generator-runtime-table', [DashboardController::class, 'generatorRuntimeTable'])->name('generator-runtime-table');
    Route::get('/runtime-table-access', function () {
        return view('runtime-table-access');
    })->name('runtime-table-access');
});
