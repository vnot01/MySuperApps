<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Api\RvmAuthController;
use App\Http\Controllers\Admin\EnhancedMetricsController;
use App\Http\Controllers\Admin\EnhancedRemoteCommandsController;

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

// RVM-Jetson API Routes (No CSRF protection needed)
Route::get('/health-check', [HealthController::class, 'check'])->name('api.health-check');
Route::get('/status', [HealthController::class, 'status'])->name('api.status');

// RVM Authentication Routes
Route::post('/rvm/generate-token', [RvmAuthController::class, 'generateToken'])->name('api.rvm.generate-token');
Route::post('/rvm/validate-token', [RvmAuthController::class, 'validateToken'])->name('api.rvm.validate-token');
Route::post('/rvm/revoke-token', [RvmAuthController::class, 'revokeToken'])->name('api.rvm.revoke-token');

// RVM-Jetson API Routes (No CSRF protection needed)
Route::get('/rvm/{id}/metrics', [EnhancedMetricsController::class, 'getComprehensiveMetrics'])->name('api.rvm.metrics');
Route::post('/rvm/{id}/store-metrics', [EnhancedMetricsController::class, 'storeMetrics'])->name('api.rvm.store-metrics');
Route::post('/rvm/{id}/execute-command', [EnhancedRemoteCommandsController::class, 'executeCommand'])->name('api.rvm.execute-command');
Route::get('/rvm/{id}/command/{commandId}/status', [EnhancedRemoteCommandsController::class, 'getCommandStatus'])->name('api.rvm.command-status');
Route::get('/rvm/{id}/recent-commands', [EnhancedRemoteCommandsController::class, 'getRecentCommands'])->name('api.rvm.recent-commands');

// Include API v2 routes
require __DIR__.'/api-v2.php';
