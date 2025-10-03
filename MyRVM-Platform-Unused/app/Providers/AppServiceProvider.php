<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\Listeners\AuthEventListener;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register authentication event listeners
        Event::listen(Login::class, [AuthEventListener::class, 'handleLogin']);
        Event::listen(Logout::class, [AuthEventListener::class, 'handleLogout']);

        // Share unread notification count with navbar component
        View::composer('components.navbar', function ($view) {
            $unreadCount = 0;
            $recentNotifications = collect();
            
            if (Auth::check()) {
                $unreadCount = Notification::forUser(Auth::id())->unread()->count();
                $recentNotifications = Notification::forUser(Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
                    
                Log::info('View Composer called', [
                    'user_id' => Auth::id(),
                    'unread_count' => $unreadCount,
                    'recent_count' => $recentNotifications->count()
                ]);
            }
            
            $view->with([
                'unreadNotificationCount' => $unreadCount,
                'recentNotifications' => $recentNotifications
            ]);
        });
    }
}
