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

# Event untuk memberitahu UI RVM bahwa sesi telah diotorisasi oleh user
class SesiDiotorisasi implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $rvmId;
    public string $userName;

    public function __construct(int $rvmId, User $user)
    {
        $this->rvmId = $rvmId;
        $this->userName = $user->name;
    }

    public function broadcastOn(): array
    {
        // Kirim event ini ke channel privat RVM yang spesifik
        return [
            new PrivateChannel('rvm.' . $this->rvmId),
        ];
    }
}
