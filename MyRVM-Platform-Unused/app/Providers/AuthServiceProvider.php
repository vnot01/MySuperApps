<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Kita akan mendaftarkan Policies di sini nanti
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        /**
         * Gate "super-gate" yang berjalan sebelum semua otorisasi lain.
         * Jika user memiliki role 'super-admin', ia diizinkan melakukan aksi apa pun.
         */
        // Gate untuk Super Admin
        Gate::before(function (User $user, string $ability) {
            // Eager load relasi untuk efisiensi jika belum di-load
            if (!$user->relationLoaded('role')) {
                $user->load('role.permissions');
            }

            // Eager load relasi role dan permissions untuk efisiensi dan keandalan
            $user->loadMissing('role.permissions');
            if ($user->role && $user->role->slug === 'super-admin') {
                return true;
            }

            return null; // Penting: kembalikan null jika bukan super-admin agar Gate lain diperiksa

            // if ($user->role?->slug === 'super-admin') {
            //     return true;
            // }
        });

        /**
         * Mendefinisikan Gate berdasarkan slug permission di database.
         * Contoh: Gate::define('manage-tenants', ...) akan memeriksa
         * apakah role user memiliki permission dengan slug 'manage.tenants'.
         */
        Gate::define('manage-tenants', function (User $user) {
            return $user->role?->permissions->contains('slug', 'manage.tenants');
        });

        Gate::define('manage-users', function (User $user) {
            return $user->role?->permissions->contains('slug', 'manage.users');
        });

        Gate::define('manage-vouchers', function (User $user) {
            return $user->role?->permissions->contains('slug', 'manage.vouchers');
        });

        Gate::define('view-dashboard', function (User $user) {
            return $user->role?->permissions->contains('slug', 'view.dashboard');
        });

        // Tambahkan definisi Gate lain di sini jika diperlukan...
    }
}
