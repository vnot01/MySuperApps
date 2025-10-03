<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class HealthController extends Controller
{
    /**
     * Health check endpoint for RVM-Jetson
     */
    public function check(Request $request): JsonResponse
    {
        try {
            $healthData = [
                'status' => 'healthy',
                'timestamp' => Carbon::now()->toISOString(),
                'server' => [
                    'name' => 'MyRVM Platform',
                    'version' => '1.0.0',
                    'environment' => app()->environment(),
                    'uptime' => $this->getServerUptime(),
                ],
                'database' => [
                    'status' => $this->checkDatabase(),
                    'connection' => config('database.default'),
                ],
                'services' => [
                    'api' => 'operational',
                    'authentication' => 'operational',
                    'metrics' => 'operational',
                    'commands' => 'operational',
                ],
                'rvm_support' => [
                    'csrf_enabled' => true,
                    'cors_enabled' => true,
                    'api_endpoints' => [
                        'health_check' => '/api/health-check',
                        'metrics' => '/admin/rvm/{id}/metrics',
                        'commands' => '/admin/rvm/{id}/execute-command',
                        'status' => '/admin/rvm/{id}/command/{commandId}/status',
                    ]
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'MyRVM Platform is healthy',
                'data' => $healthData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Health check failed',
                'error' => $e->getMessage(),
                'timestamp' => Carbon::now()->toISOString()
            ], 500);
        }
    }

    /**
     * Get server uptime
     */
    private function getServerUptime(): string
    {
        try {
            $uptime = shell_exec('uptime -p 2>/dev/null');
            return $uptime ? trim($uptime) : 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Check database connection
     */
    private function checkDatabase(): string
    {
        try {
            \DB::connection()->getPdo();
            return 'connected';
        } catch (\Exception $e) {
            return 'disconnected';
        }
    }

    /**
     * API status endpoint
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'API is operational',
            'timestamp' => Carbon::now()->toISOString(),
            'endpoints' => [
                'health' => '/api/health-check',
                'status' => '/api/status',
                'metrics' => '/admin/rvm/{id}/metrics',
                'commands' => '/admin/rvm/{id}/execute-command'
            ]
        ]);
    }
}
