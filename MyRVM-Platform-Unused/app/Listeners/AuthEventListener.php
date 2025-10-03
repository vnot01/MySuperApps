<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;
use App\Events\UserActionNotification;
use App\Models\Notification;

class AuthEventListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle user login events.
     */
    public function handleLogin(Login $event): void
    {
        try {
            $user = $event->user;
            $request = Request::instance();
            
            $details = [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_time' => now()->format('Y-m-d H:i:s'),
                'session_id' => session()->getId(),
            ];
            
            // Broadcast login notification
            broadcast(new UserActionNotification($user, 'login', null, $details));
            
            // Create notification record for admin
            $this->createLoginNotification($user, $details);
            
            Log::info("User login: {$user->name} ({$user->email}) from {$request->ip()}");
            
        } catch (\Exception $e) {
            Log::error('Failed to handle login event: ' . $e->getMessage());
        }
    }

    /**
     * Handle user logout events.
     */
    public function handleLogout(Logout $event): void
    {
        try {
            $user = $event->user;
            $request = Request::instance();
            
            $details = [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'logout_time' => now()->format('Y-m-d H:i:s'),
                'session_id' => session()->getId(),
            ];
            
            // Broadcast logout notification
            broadcast(new UserActionNotification($user, 'logout', null, $details));
            
            // Create notification record for admin
            $this->createLogoutNotification($user, $details);
            
            Log::info("User logout: {$user->name} ({$user->email}) from {$request->ip()}");
            
        } catch (\Exception $e) {
            Log::error('Failed to handle logout event: ' . $e->getMessage());
        }
    }

    /**
     * Create login notification record
     */
    private function createLoginNotification($user, array $details): void
    {
        try {
            $title = 'User Login';
            $message = "{$user->name} logged in from {$details['ip_address']} at {$details['login_time']}";
            
            Notification::createNotification([
                'notification_id' => 'login_' . $user->id . '_' . microtime(true),
                'user_id' => null, // Admin notification
                'title' => $title,
                'message' => $message,
                'type' => 'success',
                'category' => 'user_action',
                'data' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'user_role' => $user->role ?: 'user',
                    'action' => 'login',
                    'details' => $details,
                ],
                'is_system_wide' => false
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create login notification: ' . $e->getMessage());
        }
    }

    /**
     * Create logout notification record
     */
    private function createLogoutNotification($user, array $details): void
    {
        try {
            $title = 'User Logout';
            $message = "{$user->name} logged out from {$details['ip_address']} at {$details['logout_time']}";
            
            Notification::createNotification([
                'notification_id' => 'logout_' . $user->id . '_' . microtime(true),
                'user_id' => null, // Admin notification
                'title' => $title,
                'message' => $message,
                'type' => 'info',
                'category' => 'user_action',
                'data' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'user_role' => $user->role ?: 'user',
                    'action' => 'logout',
                    'details' => $details,
                ],
                'is_system_wide' => false
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create logout notification: ' . $e->getMessage());
        }
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): void
    {
        $events->listen(
            Login::class,
            [AuthEventListener::class, 'handleLogin']
        );

        $events->listen(
            Logout::class,
            [AuthEventListener::class, 'handleLogout']
        );
    }
}