<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GeneratorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Generator API Routes
Route::prefix('generator')->group(function () {
    Route::get('/status', [GeneratorController::class, 'status'])->name('api.generator.status');
    Route::get('/logs', [GeneratorController::class, 'logs'])->name('api.generator.logs');
    Route::get('/write-logs', [GeneratorController::class, 'writeLogs'])->name('api.generator.write-logs');
    Route::post('/save-logs', [GeneratorController::class, 'saveLogData'])->name('api.generator.save-logs');
    Route::post('/save-write-logs', [GeneratorController::class, 'saveWriteLogData'])->name('api.generator.save-write-logs');

    // Power status endpoint
    Route::get('/power-status', [GeneratorController::class, 'getPowerStatus'])->name('api.generator.power-status');
});
