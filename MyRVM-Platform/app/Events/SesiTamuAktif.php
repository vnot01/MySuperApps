<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SesiTamuAktif implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $rvmId;
    public $sessionToken;

    /**
     * Create a new event instance.
     */
    public function __construct($rvmId, $sessionToken)
    {
        $this->rvmId = $rvmId;
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
        return 'sesi.tamu.aktif';
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
            'session_token' => $this->sessionToken,
            'timestamp' => now()->toISOString(),
            'message' => 'Mode Donasi Aktif. Silakan masukkan item Anda.'
        ];
    }
}