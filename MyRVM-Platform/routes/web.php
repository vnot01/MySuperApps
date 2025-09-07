<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RvmUIController;

// Route::get('/', function () {
//     return view('welcome');
// });

// --- TAMBAHKAN RUTE INI ---
Route::get('/', function () {
    // Cek jika user sudah login
    if (Auth::check()) {
        $user = Auth::user();
        // Cek jika user memiliki peran yang bisa mengakses dasbor admin
        if (in_array($user->role?->slug, ['super-admin', 'admin', 'tenant'])) {
            return redirect()->route('admin.dashboard');
        }
        // Jika user biasa, arahkan ke dasbor user biasa
        return redirect()->route('dashboard');
    }
    // Jika belum login, arahkan ke halaman login
    return redirect()->route('login');
})->name('home'); // <-- Memberi nama 'home' pada rute root
// -------------------------

// Rute dasbor user biasa (dari Breeze)
Route::get('/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rute profil (dari Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Grup rute admin Anda
Route::middleware(['auth', 'verified']) // Anda bisa tambahkan 'role:...' di sini atau di grup/rute individu
    ->prefix('web')
    ->name('admin.')
    ->group(function () {
        // Contoh rute dasbor admin
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');
        // ... (rute admin lainnya: users, tenants, dll.)
    });


// RVM UI Routes (Public access for RVM displays)
Route::get('/rvm-ui/{rvm}', [RvmUIController::class, 'show'])->name('rvm.ui');

require __DIR__ . '/auth.php';
