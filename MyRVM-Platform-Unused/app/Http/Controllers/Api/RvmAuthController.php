<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReverseVendingMachine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RvmAuthController extends Controller
{
    /**
     * Generate API token for RVM
     */
    public function generateToken(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'rvm_id' => 'required|string',
                'ip_address' => 'required|ip',
            ]);

            // Find RVM
            $rvm = ReverseVendingMachine::where('id', $data['rvm_id'])
                ->orWhere('ip_address', $data['ip_address'])
                ->first();

            if (!$rvm) {
                return response()->json([
                    'success' => false,
                    'error' => 'RVM not found',
                    'message' => 'RVM with provided ID or IP not found'
                ], 404);
            }

            // Generate API token
            $apiToken = Str::random(64);
            
            // Store token in RVM record (you might want to create a separate tokens table)
            $rvm->update([
                'api_token' => hash('sha256', $apiToken),
                'api_token_expires_at' => Carbon::now()->addDays(30),
                'last_api_access' => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'API token generated successfully',
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'api_token' => $apiToken,
                    'expires_at' => $rvm->api_token_expires_at->toISOString(),
                    'server_url' => url('/'),
                    'endpoints' => [
                        'health_check' => '/api/health-check',
                        'metrics' => "/admin/rvm/{$rvm->id}/metrics",
                        'store_metrics' => "/admin/rvm/{$rvm->id}/store-metrics",
                        'execute_command' => "/admin/rvm/{$rvm->id}/execute-command",
                        'command_status' => "/admin/rvm/{$rvm->id}/command/{commandId}/status",
                        'recent_commands' => "/admin/rvm/{$rvm->id}/recent-commands"
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to generate API token'
            ], 500);
        }
    }

    /**
     * Validate API token
     */
    public function validateToken(Request $request): JsonResponse
    {
        try {
            $token = $request->header('Authorization');
            
            if (!$token || !str_starts_with($token, 'Bearer ')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid authorization header',
                    'message' => 'Bearer token required'
                ], 401);
            }

            $token = substr($token, 7); // Remove 'Bearer ' prefix
            $hashedToken = hash('sha256', $token);

            // Find RVM by token
            $rvm = ReverseVendingMachine::where('api_token', $hashedToken)
                ->where('api_token_expires_at', '>', Carbon::now())
                ->first();

            if (!$rvm) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid or expired token',
                    'message' => 'API token is invalid or has expired'
                ], 401);
            }

            // Update last access time
            $rvm->update(['last_api_access' => Carbon::now()]);

            return response()->json([
                'success' => true,
                'message' => 'Token is valid',
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'expires_at' => $rvm->api_token_expires_at->toISOString(),
                    'last_access' => $rvm->last_api_access->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Token validation failed'
            ], 500);
        }
    }

    /**
     * Revoke API token
     */
    public function revokeToken(Request $request): JsonResponse
    {
        try {
            $token = $request->header('Authorization');
            
            if (!$token || !str_starts_with($token, 'Bearer ')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid authorization header'
                ], 401);
            }

            $token = substr($token, 7);
            $hashedToken = hash('sha256', $token);

            // Find and revoke token
            $rvm = ReverseVendingMachine::where('api_token', $hashedToken)->first();
            
            if ($rvm) {
                $rvm->update([
                    'api_token' => null,
                    'api_token_expires_at' => null
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Token revoked successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to revoke token'
            ], 500);
        }
    }
}
