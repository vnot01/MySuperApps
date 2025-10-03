<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DepositCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $rvmId;
    public $depositId;
    public $sessionId;
    public $wasteType;
    public $weight;
    public $rewardAmount;

    /**
     * Create a new event instance.
     */
    public function __construct($rvmId, $depositId, $sessionId, $wasteType, $weight, $rewardAmount)
    {
        $this->rvmId = $rvmId;
        $this->depositId = $depositId;
        $this->sessionId = $sessionId;
        $this->wasteType = $wasteType;
        $this->weight = $weight;
        $this->rewardAmount = $rewardAmount;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("rvm.{$this->rvmId}"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'deposit.completed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'deposit_id' => $this->depositId,
            'session_id' => $this->sessionId,
            'waste_type' => $this->wasteType,
            'weight' => $this->weight,
            'reward_amount' => $this->rewardAmount,
            'timestamp' => now()->toISOString(),
        ];
    }
}
