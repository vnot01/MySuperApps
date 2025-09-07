<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V2\RvmSessionController;
use App\Http\Controllers\Api\V2\AuthController;
use App\Http\Controllers\Api\V2\DepositController;
use App\Http\Controllers\Api\V2\BalanceController;
use App\Http\Controllers\Api\V2\VoucherController;
use App\Http\Controllers\Api\V2\AdminController;
use App\Http\Controllers\Api\V2\TenantController;
use App\Http\Controllers\Api\V2\RVMController;
use App\Http\Controllers\Api\V2\UserManagementController;
use App\Http\Controllers\Api\V2\AnalyticsController;
use App\Http\Controllers\AdminRvmController;

/*
|--------------------------------------------------------------------------
| API v2 Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API v2 routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::prefix('v2')->group(function () {
    
    // Authentication routes
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    
    // RVM Session Management (Public endpoints)
    Route::prefix('rvm/session')->group(function () {
        Route::post('/create', [RvmSessionController::class, 'create']);
        Route::post('/activate-guest', [RvmSessionController::class, 'activateGuest']);
        Route::get('/status', [RvmSessionController::class, 'status']);
    });
    
    // RVM Management Routes (Public for Testing)
    Route::prefix('rvms')->group(function () {
        Route::get('/', [RVMController::class, 'getRVMs']);
        Route::get('/{id}', [RVMController::class, 'getRVM']);
        Route::get('/{id}/statistics', [RVMController::class, 'getRVMStatistics']);
    });
    
});

// Protected routes (authentication required)
Route::prefix('v2')->middleware('auth:sanctum')->group(function () {
    
    // Authentication routes (Protected)
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    
    // RVM Session Management (Protected endpoints)
    Route::prefix('rvm/session')->group(function () {
        Route::post('/claim', [RvmSessionController::class, 'claim']);
    });
    
    // Deposit Management (Protected endpoints)
    Route::prefix('deposits')->group(function () {
        Route::post('/', [DepositController::class, 'create']);
        Route::get('/', [DepositController::class, 'index']);
        Route::get('/statistics', [DepositController::class, 'statistics']);
        Route::get('/{id}', [DepositController::class, 'show']);
        Route::post('/{id}/process', [DepositController::class, 'process']);
    });
    
    // User Balance & Voucher Management
    // User Balance Management
    Route::prefix('user')->group(function () {
        Route::get('/balance', [BalanceController::class, 'getBalance']);
        Route::get('/balance/transactions', [BalanceController::class, 'getTransactionHistory']);
        Route::get('/balance/statistics', [BalanceController::class, 'getBalanceStatistics']);
        Route::get('/economy/summary', [BalanceController::class, 'getEconomySummary']);
    });
    
    // Voucher Management
    Route::prefix('vouchers')->group(function () {
        Route::get('/', [VoucherController::class, 'getAvailableVouchers']);
        Route::post('/redeem', [VoucherController::class, 'redeemVoucher']);
    });
    
    // Admin Management (Admin & SuperAdmin only)
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard/stats', [AdminController::class, 'getDashboardStats']);
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::post('/users', [AdminController::class, 'createUser']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        Route::get('/settings', [AdminController::class, 'getSystemSettings']);
        Route::put('/settings', [AdminController::class, 'updateSystemSettings']);
    });

    // Tenant Management Routes
    Route::prefix('tenants')->group(function () {
        Route::get('/', [TenantController::class, 'getTenants']);
        Route::get('/{id}', [TenantController::class, 'getTenant']);
        Route::post('/', [TenantController::class, 'createTenant']);
        Route::put('/{id}', [TenantController::class, 'updateTenant']);
        Route::delete('/{id}', [TenantController::class, 'deleteTenant']);
        Route::get('/{id}/statistics', [TenantController::class, 'getTenantStatistics']);
        Route::patch('/{id}/toggle-status', [TenantController::class, 'toggleTenantStatus']);
    });

    // RVM Management Routes
    Route::prefix('rvms')->group(function () {
        Route::get('/', [RVMController::class, 'getRVMs']);
        Route::get('/{id}', [RVMController::class, 'getRVM']);
        Route::post('/', [RVMController::class, 'createRVM']);
        Route::put('/{id}', [RVMController::class, 'updateRVM']);
        Route::delete('/{id}', [RVMController::class, 'deleteRVM']);
        Route::get('/{id}/statistics', [RVMController::class, 'getRVMStatistics']);
        Route::patch('/{id}/status', [RVMController::class, 'updateRVMStatus']);
        Route::patch('/{id}/regenerate-api-key', [RVMController::class, 'regenerateAPIKey']);
    });

    // User Management Routes
    Route::prefix('users')->group(function () {
        Route::get('/', [UserManagementController::class, 'getUsers']);
        Route::get('/roles', [UserManagementController::class, 'getRoles']);
        Route::get('/{id}', [UserManagementController::class, 'getUser']);
        Route::post('/', [UserManagementController::class, 'createUser']);
        Route::put('/{id}', [UserManagementController::class, 'updateUser']);
        Route::delete('/{id}', [UserManagementController::class, 'deleteUser']);
        Route::get('/{id}/statistics', [UserManagementController::class, 'getUserStatistics']);
        Route::patch('/{id}/balance', [UserManagementController::class, 'updateUserBalance']);
    });

    // Analytics & Reporting Routes
    Route::prefix('analytics')->group(function () {
        Route::get('/dashboard', [AnalyticsController::class, 'getDashboardAnalytics']);
        Route::get('/deposits', [AnalyticsController::class, 'getDepositAnalytics']);
        Route::get('/economy', [AnalyticsController::class, 'getEconomyAnalytics']);
        Route::get('/users', [AnalyticsController::class, 'getUserAnalytics']);
        Route::get('/rvms', [AnalyticsController::class, 'getRVMAnalytics']);
        Route::post('/reports', [AnalyticsController::class, 'generateReport']);
    });

    // Admin RVM Control API Routes
    Route::prefix('admin/rvm')->group(function () {
        Route::get('/list', [AdminRvmController::class, 'getRvmList']);
        Route::get('/monitoring', [AdminRvmController::class, 'getRvmMonitoring']);
        Route::get('/{rvmId}/details', [AdminRvmController::class, 'getRvmDetails']);
        Route::post('/{rvmId}/remote-access', [AdminRvmController::class, 'remoteAccess']);
        Route::post('/{rvmId}/status', [AdminRvmController::class, 'updateRvmStatus']);
        Route::put('/{rvmId}/settings', [AdminRvmController::class, 'updateRvmSettings']);
    });
});

// Public API Routes for Testing (No Authentication Required)
Route::prefix('v2')->group(function () {
    // Admin RVM Control API Routes (Public for Testing)
    Route::prefix('admin/rvm')->group(function () {
        Route::get('/list', [AdminRvmController::class, 'getRvmList']);
        Route::get('/monitoring', [AdminRvmController::class, 'getRvmMonitoring']);
        Route::get('/{rvmId}/details', [AdminRvmController::class, 'getRvmDetails']);
        Route::post('/{rvmId}/remote-access', [AdminRvmController::class, 'remoteAccess']);
        Route::post('/{rvmId}/status', [AdminRvmController::class, 'updateRvmStatus']);
        Route::put('/{rvmId}/settings', [AdminRvmController::class, 'updateRvmSettings']);
    });
});
