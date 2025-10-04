<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Pusher\Pusher;

class WebSocketController extends Controller
{
    private $pusher;

    public function __construct()
    {
        $this->pusher = new Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            [
                'cluster' => config('broadcasting.connections.pusher.options.cluster'),
                'useTLS' => true
            ]
        );
    }

    public function broadcastRvmUpdate($rvmId, $data): void
    {
        try {
            $this->pusher->trigger(
                'rvm-updates',
                'rvm-updated',
                [
                    'rvm_id' => $rvmId,
                    'data' => $data,
                    'timestamp' => now()->toISOString()
                ]
            );
        } catch (\Exception $e) {
            Log::error('WebSocket broadcast failed', [
                'rvm_id' => $rvmId,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function broadcastDetectionUpdate($rvmId, $detectionData): void
    {
        try {
            $this->pusher->trigger(
                'detection-updates',
                'detection-completed',
                [
                    'rvm_id' => $rvmId,
                    'detection' => $detectionData,
                    'timestamp' => now()->toISOString()
                ]
            );
        } catch (\Exception $e) {
            Log::error('WebSocket detection broadcast failed', [
                'rvm_id' => $rvmId,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function broadcastSystemAlert($alertData): void
    {
        try {
            $this->pusher->trigger(
                'system-alerts',
                'alert-triggered',
                [
                    'alert' => $alertData,
                    'timestamp' => now()->toISOString()
                ]
            );
        } catch (\Exception $e) {
            Log::error('WebSocket alert broadcast failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getWebSocketConfig(): JsonResponse
    {
        return response()->json([
            'key' => config('broadcasting.connections.pusher.key'),
            'cluster' => config('broadcasting.connections.pusher.options.cluster'),
            'encrypted' => true
        ]);
    }
}
