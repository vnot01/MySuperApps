<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Api\RvmAuthController;
use App\Http\Controllers\Api\V2\RVMController;
use App\Http\Controllers\Api\RvmManualTriggerController;
use App\Http\Controllers\Admin\RvmController as AdminRvmController;
use App\Http\Controllers\Admin\EnhancedMetricsController;
use App\Http\Controllers\Admin\EnhancedRemoteCommandsController;


Route::prefix('v1')->group(function () {
    Route::post('/login', [RvmAuthController::class, 'login']);
    Route::post('/logout', [RvmAuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/user', [RvmAuthController::class, 'user'])->middleware('auth:sanctum');
    Route::post('/rvm/store', [AdminRvmController::class, 'store']);
    Route::post('/rvm/complete-transaction', [AdminRvmController::class, 'completeTransaction']);
    Route::get('/rvm/settings', [AdminRvmController::class, 'getSettings']);
});

Route::prefix('v2')->group(function () {
    Route::get('/rvm', [RVMController::class, 'getRVMs']);
    Route::get('/rvm/{id}', [RVMController::class, 'getRVM']);
    Route::post('/rvm', [RVMController::class, 'createRVM']);
    Route::put('/rvm/{id}', [RVMController::class, 'updateRVM']);
});

Route::post('/rvm/trigger-pulse-check', [RvmManualTriggerController::class, 'triggerPulseCheck']);
Route::post('/rvm/trigger-health-check', [RvmManualTriggerController::class, 'triggerHealthCheck']);

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

// Notification API Routes (Protected with authentication)
Route::middleware(['auth:sanctum'])->prefix('notifications')->name('api.notifications.')->group(function () {
    Route::get('/recent', function () {
        $notifications = Notification::where('user_id', Auth::id())
            ->where('created_at', '>=', now()->subMinutes(5))
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        return response()->json($notifications);
    })->name('recent');
    
    Route::post('/{id}/read', function ($id) {
        $notification = Notification::where('user_id', Auth::id())->find($id);
        if ($notification) {
            $notification->update(['read_at' => now()]);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    })->name('read');
});

// Include API v2 routes
require __DIR__.'/api-v2.php';
