<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V2\RvmSessionController;
use App\Http\Controllers\Api\V2\AuthController;
use App\Http\Controllers\Api\V2\DepositController;

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
    Route::prefix('user')->group(function () {
        Route::get('/balance', function (Request $request) {
            return response()->json([
                'success' => true,
                'message' => 'User balance endpoint - to be implemented',
                'data' => [
                    'user_id' => $request->user()->id,
                    'balance' => 0.00
                ]
            ]);
        });
    });
    
    Route::prefix('vouchers')->group(function () {
        Route::get('/', function (Request $request) {
            return response()->json([
                'success' => true,
                'message' => 'Vouchers list endpoint - to be implemented',
                'data' => []
            ]);
        });
        
        Route::post('/redeem', function (Request $request) {
            return response()->json([
                'success' => true,
                'message' => 'Voucher redeem endpoint - to be implemented',
                'data' => []
            ]);
        });
    });
    
});
