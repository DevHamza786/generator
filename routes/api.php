 <?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GeneratorController;
use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\RuntimeController;

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
    Route::get('/quick-stats', [GeneratorController::class, 'quickStats'])->name('api.generator.quick-stats');
    Route::get('/runtime', [GeneratorController::class, 'getGeneratorRuntime'])->name('api.generator.runtime');
    Route::post('/save-logs', [GeneratorController::class, 'saveLogData'])->name('api.generator.save-logs');
    Route::post('/save-write-logs', [GeneratorController::class, 'saveWriteLogData'])->name('api.generator.save-write-logs');

    // Power status endpoint
    Route::post('/power-status', [GeneratorController::class, 'getPowerStatus'])->name('api.generator.power-status');
});

// Alert API Routes
Route::prefix('alerts')->group(function () {
    Route::get('/', [AlertController::class, 'index'])->name('api.alerts.index');
    Route::get('/stats', [AlertController::class, 'stats'])->name('api.alerts.stats');
    Route::get('/recent', [AlertController::class, 'recent'])->name('api.alerts.recent');
    Route::post('/check', [AlertController::class, 'check'])->name('api.alerts.check');
    Route::post('/{id}/acknowledge', [AlertController::class, 'acknowledge'])->name('api.alerts.acknowledge');
    Route::post('/acknowledge-all', [AlertController::class, 'acknowledgeAll'])->name('api.alerts.acknowledge-all');
    Route::post('/{id}/resolve', [AlertController::class, 'resolve'])->name('api.alerts.resolve');
});

// Runtime API Routes
Route::prefix('runtime')->group(function () {
    Route::get('/summary', [RuntimeController::class, 'summary'])->name('api.runtime.summary');
    Route::get('/running', [RuntimeController::class, 'running'])->name('api.runtime.running');
    Route::get('/analytics', [RuntimeController::class, 'analytics'])->name('api.runtime.analytics');
    Route::get('/', [RuntimeController::class, 'index'])->name('api.runtime.index');
    Route::get('/{id}', [RuntimeController::class, 'show'])->name('api.runtime.show');
    Route::get('/generator/{generatorId}/stats', [RuntimeController::class, 'generatorStats'])->name('api.runtime.generator-stats');
    Route::post('/process', [RuntimeController::class, 'process'])->name('api.runtime.process');
    Route::post('/{id}/stop', [RuntimeController::class, 'stop'])->name('api.runtime.stop');
});
