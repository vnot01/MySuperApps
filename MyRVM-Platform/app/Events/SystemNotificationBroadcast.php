<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SystemNotificationBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;
    public $targetType;
    public $targetIds;
    public $timestamp;

    /**
     * Create a new event instance.
     *
     * @param array $notification
     * @param string $targetType ('all', 'tenants', 'users', 'specific')
     * @param array $targetIds (optional, for specific users/tenants)
     */
    public function __construct($notification, $targetType = 'all', $targetIds = [])
    {
        $this->notification = $notification;
        $this->targetType = $targetType;
        $this->targetIds = $targetIds;
        $this->timestamp = now();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];
        
        switch ($this->targetType) {
            case 'all':
                $channels[] = new Channel('system-notifications');
                $channels[] = new Channel('admin-dashboard');
                break;
                
            case 'tenants':
                $channels[] = new Channel('tenant-notifications');
                $channels[] = new Channel('admin-dashboard');
                break;
                
            case 'users':
                $channels[] = new Channel('user-notifications');
                $channels[] = new Channel('admin-dashboard');
                break;
                
            case 'specific':
                foreach ($this->targetIds as $id) {
                    $channels[] = new PrivateChannel('user.' . $id);
                }
                $channels[] = new Channel('admin-dashboard');
                break;
                
            default:
                $channels[] = new Channel('system-notifications');
                break;
        }
        
        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'system.notification.broadcast';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'notification_id' => $this->notification['notification_id'],
            'title' => $this->notification['title'],
            'message' => $this->notification['message'],
            'type' => $this->notification['type'],
            'category' => 'system',
            'target_type' => $this->targetType,
            'target_ids' => $this->targetIds,
            'data' => $this->notification['data'] ?? [],
            'is_system_wide' => $this->notification['is_system_wide'] ?? true,
            'timestamp' => $this->timestamp->toISOString(),
            'created_at' => $this->timestamp->format('Y-m-d H:i:s'),
        ];
    }
}