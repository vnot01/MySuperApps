<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V2\RvmSessionController;
use App\Http\Controllers\Api\V2\AuthController;
use App\Http\Controllers\Api\V2\DepositController;
use App\Http\Controllers\Api\V2\BalanceController;
use App\Http\Controllers\Api\V2\VoucherController;

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
    
});
