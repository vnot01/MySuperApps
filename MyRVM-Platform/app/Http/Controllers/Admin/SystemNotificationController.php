<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Events\SystemNotificationBroadcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SystemNotificationController extends Controller
{
    /**
     * Display system notifications management page
     */
    public function index()
    {
        $systemNotifications = Notification::where('category', 'system')
            ->where('is_system_wide', true)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.notifications.system', compact('systemNotifications'));
    }

    /**
     * Show form to create new system notification
     */
    public function create()
    {
        $users = User::select('id', 'name', 'email')->get();
        return view('admin.notifications.create-system', compact('users'));
    }

    /**
     * Store new system notification
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:info,success,warning,error',
            'target_type' => 'required|in:all,tenants,users,specific',
            'target_ids' => 'nullable|array',
            'target_ids.*' => 'exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'expires_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $notificationId = 'system_' . Str::random(16) . '_' . time();
            
            $notificationData = [
                'notification_id' => $notificationId,
                'title' => $request->title,
                'message' => $request->message,
                'type' => $request->type,
                'category' => 'system',
                'data' => [
                    'priority' => $request->priority,
                    'target_type' => $request->target_type,
                    'target_ids' => $request->target_ids ?? [],
                    'expires_at' => $request->expires_at,
                    'created_by' => Auth::user()->name,
                    'created_by_id' => Auth::id(),
                ],
                'is_system_wide' => true,
            ];

            // Create notification records based on target type
            $this->createNotificationRecords($notificationData, $request->target_type, $request->target_ids ?: []);

            // Broadcast the notification
            broadcast(new SystemNotificationBroadcast(
                $notificationData,
                $request->target_type,
                $request->target_ids ?? []
            ));

            return redirect()->route('admin.notifications.system.index')
                ->with('success', 'System notification broadcasted successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to create system notification: ' . $e->getMessage());
            return back()->with('error', 'Failed to create system notification: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Create notification records in database
     */
    private function createNotificationRecords($notificationData, $targetType, $targetIds = [])
    {
        switch ($targetType) {
            case 'all':
                // Create one system-wide notification
                Notification::createNotification([
                    'notification_id' => $notificationData['notification_id'],
                    'user_id' => null, // user_id null for system-wide
                    'title' => $notificationData['title'],
                    'message' => $notificationData['message'],
                    'type' => $notificationData['type'],
                    'category' => $notificationData['category'],
                    'data' => $notificationData['data'],
                    'is_system_wide' => true
                ]);
                break;

            case 'tenants':
                // Create notifications for all tenant users
                $tenantUsers = User::where('role', 'tenant')->get();
                foreach ($tenantUsers as $user) {
                    Notification::createNotification([
                        'notification_id' => $notificationData['notification_id'] . '_' . $user->id,
                        'user_id' => $user->id,
                        'title' => $notificationData['title'],
                        'message' => $notificationData['message'],
                        'type' => $notificationData['type'],
                        'category' => $notificationData['category'],
                        'data' => $notificationData['data'],
                        'is_system_wide' => false
                    ]);
                }
                break;

            case 'users':
                // Create notifications for all regular users
                $regularUsers = User::where('role', '!=', 'admin')->get();
                foreach ($regularUsers as $user) {
                    Notification::createNotification([
                        'notification_id' => $notificationData['notification_id'] . '_' . $user->id,
                        'user_id' => $user->id,
                        'title' => $notificationData['title'],
                        'message' => $notificationData['message'],
                        'type' => $notificationData['type'],
                        'category' => $notificationData['category'],
                        'data' => $notificationData['data'],
                        'is_system_wide' => false
                    ]);
                }
                break;

            case 'specific':
                // Create notifications for specific users
                foreach ($targetIds as $userId) {
                    Notification::createNotification([
                        'notification_id' => $notificationData['notification_id'] . '_' . $userId,
                        'user_id' => $userId,
                        'title' => $notificationData['title'],
                        'message' => $notificationData['message'],
                        'type' => $notificationData['type'],
                        'category' => $notificationData['category'],
                        'data' => $notificationData['data'],
                        'is_system_wide' => false
                    ]);
                }
                break;
        }
    }

    /**
     * Show specific system notification
     */
    public function show($id)
    {
        $notification = Notification::where('category', 'system')
            ->where('id', $id)
            ->firstOrFail();

        return view('admin.notifications.show-system', compact('notification'));
    }

    /**
     * Delete system notification
     */
    public function destroy($id)
    {
        try {
            $notification = Notification::where('category', 'system')
                ->where('id', $id)
                ->firstOrFail();

            $notification->delete();

            return redirect()->route('admin.notifications.system.index')
                ->with('success', 'System notification deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete notification: ' . $e->getMessage());
        }
    }

    /**
     * Get system notification statistics
     */
    public function statistics()
    {
        $stats = [
            'total_system_notifications' => Notification::where('category', 'system')->count(),
            'active_system_notifications' => Notification::where('category', 'system')
                ->where('is_read', false)
                ->count(),
            'system_notifications_today' => Notification::where('category', 'system')
                ->whereDate('created_at', today())
                ->count(),
            'system_notifications_this_week' => Notification::where('category', 'system')
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
        ];

        return response()->json($stats);
    }
}