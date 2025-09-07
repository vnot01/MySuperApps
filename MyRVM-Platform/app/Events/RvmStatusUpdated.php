<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RvmStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $rvm;
    public $status;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct($rvm, $status)
    {
        $this->rvm = $rvm;
        $this->status = $status;
        $this->timestamp = now();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('rvm-status'),
            new PrivateChannel('admin-dashboard'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'rvm.status.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'rvm_id' => $this->rvm->id,
            'rvm_name' => $this->rvm->name,
            'status' => $this->status,
            'timestamp' => $this->timestamp->toISOString(),
            'location' => $this->rvm->location,
        ];
    }
}
