<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Display the admin profile page.
     */
    public function adminProfile(Request $request): View
    {
        $user = $request->user()->load('role');
        
        return view('admin.profile.index', [
            'user' => $user,
        ]);
    }

    /**
     * Display the admin notifications page.
     */
    public function notifications(Request $request): View
    {
        $user = $request->user();
        
        // Generate notifications from real data if needed
        $this->generateNotificationsFromRealData($user);
        
        // Get tab filter from request
        $tab = $request->get('tab', 'all'); // all, unread, read
        
        // Build query with tab filtering
        $query = Notification::forUser($user->id);
        
        switch ($tab) {
            case 'unread':
                $query->unread();
                break;
            case 'read':
                $query->read();
                break;
            default:
                // 'all' - no additional filter
                break;
        }
        
        // Apply unread-first ordering, then by newest time
        $notifications = $query
            ->orderByRaw('read_at IS NULL DESC') // Unread first
            ->orderBy('created_at', 'desc')      // Then newest
            ->paginate(10);                      // 10 per page
        
        // Preserve tab parameter in pagination links
        $notifications->appends(['tab' => $tab]);
        
        // Get counts for tab badges
        $allCount = Notification::forUser($user->id)->count();
        $unreadCount = Notification::forUser($user->id)->unread()->count();
        $readCount = Notification::forUser($user->id)->read()->count();

        return view('admin.profile.notifications', [
            'user' => $user,
            'notifications' => $notifications,
            'currentTab' => $tab,
            'allCount' => $allCount,
            'unreadCount' => $unreadCount,
            'readCount' => $readCount
        ]);
    }

    /**
     * Generate notifications from real system data
     */
    private function generateNotificationsFromRealData($user): void
    {
        // 1. Check for new RVM status changes
        $rvms = DB::table('reverse_vending_machines')
            ->select('id', 'name', 'status', 'connection_status', 'last_ping', 'updated_at')
            ->get();
            
        foreach ($rvms as $rvm) {
            $notificationId = 'rvm_status_' . $rvm->id . '_' . $rvm->connection_status;
            
            // Check if this notification already exists
            $exists = Notification::where('notification_id', $notificationId)->exists();
            
            if (!$exists) {
                if ($rvm->connection_status === 'connected') {
                    Notification::createNotification([
                        'notification_id' => $notificationId,
                        'title' => 'RVM Status Update',
                        'message' => $rvm->name . ' is now connected and operational',
                        'type' => 'success',
                        'category' => 'rvm_status',
                        'data' => ['rvm_id' => $rvm->id, 'rvm_name' => $rvm->name],
                        'is_system_wide' => true
                    ]);
                } elseif ($rvm->connection_status === 'unknown' || $rvm->status === 'inactive') {
                    Notification::createNotification([
                        'notification_id' => $notificationId,
                        'title' => 'RVM Connection Issue',
                        'message' => $rvm->name . ' appears to be offline or disconnected',
                        'type' => 'warning',
                        'category' => 'rvm_status',
                        'data' => ['rvm_id' => $rvm->id, 'rvm_name' => $rvm->name],
                        'is_system_wide' => true
                    ]);
                }
            }
        }
        
        // 2. Generate notifications for recent transactions (last 24 hours)
        $recentTransactions = DB::table('transactions')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->select('transactions.*', 'users.name as user_name')
            ->where('transactions.created_at', '>=', now()->subDay())
            ->orderBy('transactions.created_at', 'desc')
            ->limit(10)
            ->get();
            
        foreach ($recentTransactions as $transaction) {
            $notificationId = 'transaction_' . $transaction->id;
            
            // Check if this notification already exists
            $exists = Notification::where('notification_id', $notificationId)->exists();
            
            if (!$exists) {
                Notification::createNotification([
                    'notification_id' => $notificationId,
                    'title' => 'New Transaction',
                    'message' => $transaction->user_name . ' received ' . number_format($transaction->amount, 2) . ' points from ' . str_replace('_', ' ', $transaction->type),
                    'type' => 'info',
                    'category' => 'transaction',
                    'data' => [
                        'transaction_id' => $transaction->id,
                        'user_id' => $transaction->user_id,
                        'amount' => $transaction->amount,
                        'type' => $transaction->type
                    ],
                    'is_system_wide' => true
                ]);
            }
        }
    }

    /**
     * Mark a notification as read
     */
    public function markNotificationAsRead(Request $request, $notificationId)
    {
        $user = $request->user();
        
        $notification = Notification::where('notification_id', $notificationId)
            ->forUser($user->id)
            ->first();
            
        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true, 'message' => 'Notification marked as read']);
        }
        
        return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsAsRead(Request $request)
    {
        $user = $request->user();
        
        $count = Notification::forUser($user->id)
            ->unread()
            ->update(['read_at' => now()]);
            
        return response()->json([
            'success' => true, 
            'message' => "Marked {$count} notifications as read",
            'count' => $count
        ]);
    }

    /**
     * Get notifications for auto refresh (AJAX endpoint)
     */
    public function getNotificationsForRefresh(Request $request)
    {
        $user = $request->user();
        
        // Generate notifications from real data if needed
        $this->generateNotificationsFromRealData($user);
        
        // Get latest notifications
        $notifications = Notification::forUser($user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Get unread count
        $unreadCount = Notification::forUser($user->id)->unread()->count();

        return response()->json([
            'success' => true,
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'notification_id' => $notification->notification_id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'category' => $notification->category,
                    'is_read' => $notification->isRead(),
                    'created_at' => $notification->created_at->diffForHumans(),
                    'created_at_iso' => $notification->created_at->toISOString(),
                    'data' => $notification->data
                ];
            }),
            'unread_count' => $unreadCount,
            'total_count' => $notifications->count()
        ]);
    }

    /**
     * Display the admin connections page.
     */
    public function connections(Request $request): View
    {
        // Get user connections/integrations (you can implement this later)
        $connections = collect([
            [
                'id' => 1,
                'name' => 'Google Analytics',
                'description' => 'Track website analytics and user behavior',
                'status' => 'connected',
                'icon' => 'fab fa-google',
                'connected_at' => now()->subDays(10)
            ],
            [
                'id' => 2,
                'name' => 'Slack Integration',
                'description' => 'Receive notifications in your Slack workspace',
                'status' => 'disconnected',
                'icon' => 'fab fa-slack',
                'connected_at' => null
            ],
            [
                'id' => 3,
                'name' => 'Email Service',
                'description' => 'Send automated emails and notifications',
                'status' => 'connected',
                'icon' => 'fas fa-envelope',
                'connected_at' => now()->subDays(5)
            ]
        ]);

        return view('admin.profile.connections', [
            'user' => $request->user(),
            'connections' => $connections
        ]);
    }
}
