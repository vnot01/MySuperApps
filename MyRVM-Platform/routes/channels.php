<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ReverseVendingMachine;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Channel untuk setiap mesin RVM.
 * Hanya RVM yang terautentikasi (melalui aplikasi Python-nya nanti) atau
 * pengguna yang berwenang (misalnya, admin di dasbornya) yang bisa mendengarkan.
 * Untuk saat ini, kita buat otorisasi sederhana yang selalu mengizinkan.
 * Kita akan memperketat ini nanti jika diperlukan.
 */
Broadcast::channel('rvm.{rvmId}', function ($user, $rvmId) {
    // Logika otorisasi: Siapa yang boleh mendengarkan channel ini?
    // Contoh: Apakah $user adalah admin? Atau apakah ini request dari RVM itu sendiri?
    // Untuk development awal, kita bisa selalu mengizinkannya.
    // Pastikan $rvmId yang diberikan adalah RVM yang valid.
    return ReverseVendingMachine::where('id', $rvmId)->exists();
});

/**
 * Channel untuk admin dashboard.
 * Hanya admin yang terautentikasi yang bisa mendengarkan.
 */
Broadcast::channel('admin-dashboard', function ($user) {
    // Check if user is authenticated and has admin role
    return $user && in_array($user->role?->slug ?? 'user', ['super-admin', 'admin', 'operator', 'technician']);
});

/**
 * Channel untuk RVM status updates.
 * Public channel untuk status updates.
 */
Broadcast::channel('rvm-status', function () {
    return true; // Public channel for status updates
});
