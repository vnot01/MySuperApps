<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

# Event untuk memberitahu UI RVM dan aplikasi Python tentang hasil analisis item
class AnalisisSelesai implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $rvmId;
    public array $hasilAnalisis; // e.g., ['item' => 'PET_BOTTLE', 'reward' => 100]

    public function __construct(int $rvmId, array $hasilAnalisis)
    {
        $this->rvmId = $rvmId;
        $this->hasilAnalisis = $hasilAnalisis;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('rvm.' . $this->rvmId),
        ];
    }
}
