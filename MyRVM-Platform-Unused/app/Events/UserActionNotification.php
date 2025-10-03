<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UserActionNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $action;
    public $resource;
    public $details;
    public $timestamp;
    public $notificationId;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param string $action (login, logout, create, update, delete, view)
     * @param string $resource (user, rvm, deposit, etc.)
     * @param array $details
     */
    public function __construct(User $user, string $action, string $resource = null, array $details = [])
    {
        $this->user = $user;
        $this->action = $action;
        $this->resource = $resource;
        $this->details = $details;
        $this->timestamp = now();
        $this->notificationId = 'user_action_' . $user->id . '_' . time() . '_' . substr(md5($action . $resource), 0, 8);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];
        
        // Always broadcast to admin dashboard
        $channels[] = new Channel('admin-dashboard');
        
        // Broadcast to user's private channel for their own actions
        $channels[] = new PrivateChannel('user.' . $this->user->id);
        
        // For login/logout, also broadcast to general user activity channel
        if (in_array($this->action, ['login', 'logout'])) {
            $channels[] = new Channel('user-activity');
        }
        
        // For CRUD operations, broadcast to resource-specific channels
        if ($this->resource && in_array($this->action, ['create', 'update', 'delete'])) {
            $channels[] = new Channel('resource-activity.' . $this->resource);
        }
        
        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'user.action.notification';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'notification_id' => $this->notificationId,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'role' => $this->user->role ?? 'user',
            ],
            'action' => $this->action,
            'resource' => $this->resource,
            'details' => $this->details,
            'category' => 'user_action',
            'type' => $this->getNotificationType(),
            'title' => $this->generateTitle(),
            'message' => $this->generateMessage(),
            'timestamp' => $this->timestamp->toISOString(),
            'created_at' => $this->timestamp->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Generate notification type based on action
     */
    private function getNotificationType(): string
    {
        switch ($this->action) {
            case 'login':
                return 'success';
            case 'logout':
                return 'info';
            case 'create':
                return 'success';
            case 'update':
                return 'info';
            case 'delete':
                return 'warning';
            case 'view':
                return 'info';
            default:
                return 'info';
        }
    }

    /**
     * Generate notification title
     */
    private function generateTitle(): string
    {
        $actionText = ucfirst($this->action);
        
        switch ($this->action) {
            case 'login':
                return 'User Login';
            case 'logout':
                return 'User Logout';
            case 'create':
                return $this->resource ? "New {$this->resource} Created" : 'New Item Created';
            case 'update':
                return $this->resource ? "{$this->resource} Updated" : 'Item Updated';
            case 'delete':
                return $this->resource ? "{$this->resource} Deleted" : 'Item Deleted';
            case 'view':
                return $this->resource ? "{$this->resource} Viewed" : 'Item Viewed';
            default:
                return "User {$actionText}";
        }
    }

    /**
     * Generate notification message
     */
    private function generateMessage(): string
    {
        $userName = $this->user->name;
        $userRole = $this->user->role ?? 'user';
        
        switch ($this->action) {
            case 'login':
                $loginTime = $this->timestamp->format('H:i:s');
                $ipAddress = $this->details['ip_address'] ?? 'Unknown IP';
                return "{$userName} ({$userRole}) logged in at {$loginTime} from {$ipAddress}";
                
            case 'logout':
                $logoutTime = $this->timestamp->format('H:i:s');
                return "{$userName} ({$userRole}) logged out at {$logoutTime}";
                
            case 'create':
                $resourceName = $this->details['resource_name'] ?? $this->resource ?? 'item';
                return "{$userName} created a new {$resourceName}";
                
            case 'update':
                $resourceName = $this->details['resource_name'] ?? $this->resource ?? 'item';
                $resourceId = $this->details['resource_id'] ?? '';
                return "{$userName} updated {$resourceName}" . ($resourceId ? " (ID: {$resourceId})" : '');
                
            case 'delete':
                $resourceName = $this->details['resource_name'] ?? $this->resource ?? 'item';
                $resourceId = $this->details['resource_id'] ?? '';
                return "{$userName} deleted {$resourceName}" . ($resourceId ? " (ID: {$resourceId})" : '');
                
            case 'view':
                $resourceName = $this->details['resource_name'] ?? $this->resource ?? 'item';
                return "{$userName} viewed {$resourceName}";
                
            default:
                return "{$userName} performed {$this->action}" . ($this->resource ? " on {$this->resource}" : '');
        }
    }
}