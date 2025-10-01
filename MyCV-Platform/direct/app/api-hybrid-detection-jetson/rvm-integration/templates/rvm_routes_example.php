<?php
/**
 * RVM Integration Routes
 * 
 * Add these routes to your MyRVM-Platform Laravel application
 * in routes/api.php or create a separate route file.
 */

use App\Http\Controllers\Api\RvmIntegrationController;
use Illuminate\Support\Facades\Route;

// RVM Integration API Routes
Route::prefix('rvm')->group(function () {
    
    // Validate RVM API key
    Route::post('validate-api-key', [RvmIntegrationController::class, 'validateApiKey'])
        ->name('rvm.validate-api-key');
    
    // Get RVM information
    Route::get('{id}', [RvmIntegrationController::class, 'getRvm'])
        ->name('rvm.show')
        ->where('id', '[0-9]+');
    
    // Get RVM statistics
    Route::get('{id}/stats', [RvmIntegrationController::class, 'getRvmStats'])
        ->name('rvm.stats')
        ->where('id', '[0-9]+');
    
    // Get RVM detection results
    Route::get('{id}/detections', [RvmIntegrationController::class, 'getRvmDetections'])
        ->name('rvm.detections')
        ->where('id', '[0-9]+');
    
    // Update RVM status
    Route::patch('{id}/status', [RvmIntegrationController::class, 'updateRvmStatus'])
        ->name('rvm.update-status')
        ->where('id', '[0-9]+');
});

// Detection Results API Routes
Route::prefix('detections')->group(function () {
    
    // Store detection result from MyCV-Platform
    Route::post('store', [RvmIntegrationController::class, 'storeDetection'])
        ->name('detections.store');
    
    // Get all detection results (admin only)
    Route::get('/', [RvmIntegrationController::class, 'getAllDetections'])
        ->name('detections.index')
        ->middleware('auth:sanctum');
    
    // Get detection result by ID
    Route::get('{id}', [RvmIntegrationController::class, 'getDetection'])
        ->name('detections.show')
        ->where('id', '[0-9]+')
        ->middleware('auth:sanctum');
    
    // Update detection result status
    Route::patch('{id}/status', [RvmIntegrationController::class, 'updateDetectionStatus'])
        ->name('detections.update-status')
        ->where('id', '[0-9]+')
        ->middleware('auth:sanctum');
    
    // Delete detection result
    Route::delete('{id}', [RvmIntegrationController::class, 'deleteDetection'])
        ->name('detections.delete')
        ->where('id', '[0-9]+')
        ->middleware('auth:sanctum');
});

// RVM Health Check Routes
Route::prefix('rvm-health')->group(function () {
    
    // RVM ping endpoint
    Route::post('ping', [RvmIntegrationController::class, 'ping'])
        ->name('rvm-health.ping');
    
    // RVM status update
    Route::post('status', [RvmIntegrationController::class, 'updateStatus'])
        ->name('rvm-health.status');
    
    // RVM metrics
    Route::post('metrics', [RvmIntegrationController::class, 'storeMetrics'])
        ->name('rvm-health.metrics');
});

// Analytics and Reporting Routes
Route::prefix('analytics')->group(function () {
    
    // Get RVM analytics
    Route::get('rvm/{id}', [RvmIntegrationController::class, 'getRvmAnalytics'])
        ->name('analytics.rvm')
        ->where('id', '[0-9]+')
        ->middleware('auth:sanctum');
    
    // Get detection analytics
    Route::get('detections', [RvmIntegrationController::class, 'getDetectionAnalytics'])
        ->name('analytics.detections')
        ->middleware('auth:sanctum');
    
    // Get system overview
    Route::get('overview', [RvmIntegrationController::class, 'getSystemOverview'])
        ->name('analytics.overview')
        ->middleware('auth:sanctum');
});

// Webhook Routes for Real-time Updates
Route::prefix('webhooks')->group(function () {
    
    // Detection completed webhook
    Route::post('detection-completed', [RvmIntegrationController::class, 'detectionCompleted'])
        ->name('webhooks.detection-completed');
    
    // RVM status changed webhook
    Route::post('rvm-status-changed', [RvmIntegrationController::class, 'rvmStatusChanged'])
        ->name('webhooks.rvm-status-changed');
    
    // Error notification webhook
    Route::post('error-notification', [RvmIntegrationController::class, 'errorNotification'])
        ->name('webhooks.error-notification');
});

// Additional RVM Integration Routes
Route::prefix('integration')->group(function () {
    
    // Test RVM connection
    Route::post('test-connection', [RvmIntegrationController::class, 'testConnection'])
        ->name('integration.test-connection');
    
    // Sync RVM data
    Route::post('sync-rvm-data', [RvmIntegrationController::class, 'syncRvmData'])
        ->name('integration.sync-rvm-data')
        ->middleware('auth:sanctum');
    
    // Export RVM data
    Route::get('export-rvm-data/{id}', [RvmIntegrationController::class, 'exportRvmData'])
        ->name('integration.export-rvm-data')
        ->where('id', '[0-9]+')
        ->middleware('auth:sanctum');
    
    // Import RVM configuration
    Route::post('import-rvm-config', [RvmIntegrationController::class, 'importRvmConfig'])
        ->name('integration.import-rvm-config')
        ->middleware('auth:sanctum');
});
