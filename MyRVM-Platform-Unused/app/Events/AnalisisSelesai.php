<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnalisisSelesai implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $rvmId;
    public $depositId;
    public $analysisResult;

    /**
     * Create a new event instance.
     */
    public function __construct(int $rvmId, int $depositId, array $analysisResult)
    {
        $this->rvmId = $rvmId;
        $this->depositId = $depositId;
        $this->analysisResult = $analysisResult;
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
        return 'analisis.selesai';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'deposit_id' => $this->depositId,
            'rvm_id' => $this->rvmId,
            'waste_type' => $this->analysisResult['waste_type'],
            'quality_grade' => $this->analysisResult['quality_grade'],
            'ai_confidence' => $this->analysisResult['ai_confidence'],
            'reward_amount' => $this->analysisResult['reward_amount'],
            'status' => $this->analysisResult['status'],
            'analysis_details' => $this->analysisResult['analysis_details'],
            'timestamp' => now()->toISOString(),
        ];
    }
}