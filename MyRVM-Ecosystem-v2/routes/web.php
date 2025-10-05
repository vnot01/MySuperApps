<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Api\RvmController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\PlaygroundController;

// Landing Page Route
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // RVM Management (Web routes for authenticated users)
    Route::post('/rvms', [RvmController::class, 'store'])->name('rvms.store');
    Route::get('/rvms/{rvm}', [RvmController::class, 'show'])->name('rvms.show');
    Route::put('/rvms/{rvm}/api', [RvmController::class, 'updateApi'])->name('rvms.api.update');
    Route::put('/rvms/{rvm}/ip-address', [MaintenanceController::class, 'updateIpAddress'])->name('rvms.ip-address.update');
    Route::get('/rvms/{rvm}/edit', [RvmController::class, 'edit'])->name('rvms.edit');
    Route::put('/rvms/{rvm}', [RvmController::class, 'update'])->name('rvms.update');
    Route::delete('/rvms/{rvm}', [RvmController::class, 'destroy'])->name('rvms.destroy');
    
    // Maintenance Routes
    Route::get('/maintenance/{rvm}', [MaintenanceController::class, 'show'])->name('maintenance.show');
    Route::post('/maintenance/{rvm}/monitoring', [MaintenanceController::class, 'storeMonitoringData'])->name('maintenance.monitoring.store');
    Route::post('/maintenance/{rvm}/end', [MaintenanceController::class, 'endMaintenance'])->name('maintenance.end');
    
    // Playground Routes
    Route::get('/playground/{rvm}', [PlaygroundController::class, 'show'])->name('playground.show');
    
    // Camera API Routes
    Route::get('/playground/{rvm}/cameras/dashboard', [PlaygroundController::class, 'getCameraDashboard'])->name('playground.cameras.dashboard');
    Route::get('/playground/{rvm}/cameras/remote', [PlaygroundController::class, 'getRemoteCameraInfo'])->name('playground.cameras.remote');
    Route::get('/playground/{rvm}/cameras/status/simple', [PlaygroundController::class, 'getSimpleCameraStatus'])->name('playground.cameras.status.simple');
    Route::get('/playground/{rvm}/cameras/discovery', [PlaygroundController::class, 'getCameraDiscovery'])->name('playground.cameras.discovery');
    Route::post('/playground/{rvm}/cameras/{cameraId}/start', [PlaygroundController::class, 'startCamera'])->name('playground.cameras.start');
    Route::post('/playground/{rvm}/cameras/{cameraId}/capture', [PlaygroundController::class, 'captureImage'])->name('playground.cameras.capture');
    Route::post('/playground/{rvm}/cameras/{cameraId}/capture/base64', [PlaygroundController::class, 'captureImageBase64'])->name('playground.cameras.capture.base64');
    Route::post('/playground/{rvm}/cameras/{cameraId}/capture-save', [PlaygroundController::class, 'captureAndSaveImage'])->name('playground.cameras.capture.save');
});

// Testing route (no auth required)
Route::get('/test-maintenance/{rvm}', [MaintenanceController::class, 'show'])->name('test.maintenance.show');
