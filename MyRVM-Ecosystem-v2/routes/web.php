<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Api\RvmController;

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
    Route::get('/rvms/{rvm}/edit', [RvmController::class, 'edit'])->name('rvms.edit');
    Route::put('/rvms/{rvm}', [RvmController::class, 'update'])->name('rvms.update');
    Route::delete('/rvms/{rvm}', [RvmController::class, 'destroy'])->name('rvms.destroy');
});
