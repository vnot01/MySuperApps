<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\RvmController;
use App\Http\Controllers\Api\RvmIntegrationController;
use App\Http\Controllers\Api\DetectionResultController;
use App\Http\Controllers\Api\EconomyController;
use App\Http\Controllers\Api\AnalyticsController;

// Public API Routes
Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

Route::post('/login', [AuthController::class, 'apiLogin']);

// Detection Management Routes (Public for Jetson access)
Route::prefix('detections')->group(function () {
    Route::post('/store', [DetectionResultController::class, 'store'])
        ->name('detections.store.public');
    Route::get('/statistics', [DetectionResultController::class, 'statistics'])
        ->name('detections.statistics.public');
});

// Monitoring Routes (Public for Jetson access)
Route::prefix('maintenance')->group(function () {
    Route::post('/{rvm}/monitoring', [\App\Http\Controllers\MaintenanceController::class, 'storeMonitoringData'])
        ->name('maintenance.monitoring.store.public');
});

// Protected API Routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::get('/user', [AuthController::class, 'apiUser']);
    Route::post('/logout', [AuthController::class, 'apiLogout']);
    
    // RVM Management
    Route::apiResource('rvms', RvmController::class);
    Route::post('/rvms/{rvm}/status', [RvmController::class, 'updateStatus']);
    Route::post('/rvms/{rvm}/metrics', [RvmController::class, 'updateMetrics']);
    Route::post('/rvms/{rvm}/ping', [RvmController::class, 'ping']);
    Route::get('/rvms-statistics', [RvmController::class, 'statistics']);
    
    // Health Check
    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'message' => 'MyRVM Ecosystem API is healthy',
            'timestamp' => now()->toISOString(),
            'version' => '2.0',
            'database' => [
                'status' => 'connected',
                'rvms_count' => \App\Models\ReverseVendingMachine::count(),
                'users_count' => \App\Models\User::count()
            ]
        ]);
    });
    
    // Detection Results Management
    Route::apiResource('detections', DetectionResultController::class);
    Route::get('/detections-statistics', [DetectionResultController::class, 'statistics']);
    Route::get('/detections-recent', [DetectionResultController::class, 'recent']);
    
    // Economy System Routes
    Route::prefix('economy')->group(function () {
        Route::get('/balance', [EconomyController::class, 'getBalance']);
        Route::get('/transactions', [EconomyController::class, 'getTransactions']);
        Route::post('/balance/add', [EconomyController::class, 'addBalance']);
        Route::post('/balance/deduct', [EconomyController::class, 'deductBalance']);
        Route::get('/vouchers/available', [EconomyController::class, 'getAvailableVouchers']);
        Route::post('/vouchers/redeem', [EconomyController::class, 'redeemVoucher']);
        Route::get('/vouchers/history', [EconomyController::class, 'getVoucherHistory']);
        Route::post('/rewards/calculate', [EconomyController::class, 'calculateReward']);
    });
    
    // Analytics Routes
    Route::prefix('analytics')->group(function () {
        Route::get('/dashboard', [AnalyticsController::class, 'getDashboardAnalytics']);
        Route::get('/rvm/{rvmId}', [AnalyticsController::class, 'getRvmAnalytics']);
        Route::get('/detections', [AnalyticsController::class, 'getDetectionAnalytics']);
    });
});

// RVM Integration Routes (Public for Jetson access)
Route::prefix('rvm')->group(function () {
    Route::post('/validate', [RvmIntegrationController::class, 'validateApiKey'])
        ->name('rvm.validate-api-key');
    
    Route::get('/{id}', [RvmIntegrationController::class, 'getRvm'])
        ->name('rvm.show')
        ->where('id', '[0-9]+');
    
    Route::get('/{id}/stats', [RvmIntegrationController::class, 'getRvmStats'])
        ->name('rvm.stats')
        ->where('id', '[0-9]+');
    
    Route::get('/{id}/detections', [RvmIntegrationController::class, 'getRvmDetections'])
        ->name('rvm.detections')
        ->where('id', '[0-9]+');
    
    Route::patch('/{id}/status', [RvmIntegrationController::class, 'updateRvmStatus'])
        ->name('rvm.update-status')
        ->where('id', '[0-9]+');
});

// RVM Status Check API (for frontend auto refresh)
Route::post('/rvm/check-status', function() {
    Artisan::call('rvm:check-status');
    return response()->json(['success' => true, 'message' => 'Status check completed']);
})->name('api.rvm.check-status');
