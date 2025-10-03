<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SesiDiotorisasi implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $rvmId;
    public $userName;
    public $sessionToken;

    /**
     * Create a new event instance.
     */
    public function __construct($rvmId, $userName, $sessionToken)
    {
        $this->rvmId = $rvmId;
        $this->userName = $userName;
        $this->sessionToken = $sessionToken;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('rvm.' . $this->rvmId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'sesi.diotorisasi';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'rvm_id' => $this->rvmId,
            'user_name' => $this->userName,
            'session_token' => $this->sessionToken,
            'timestamp' => now()->toISOString(),
            'message' => "Selamat datang, {$this->userName}! Silakan masukkan item Anda."
        ];
    }
}