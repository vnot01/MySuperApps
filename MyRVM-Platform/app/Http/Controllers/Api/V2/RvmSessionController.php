<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\ReverseVendingMachine;
use App\Events\SesiDiotorisasi;
use App\Events\SesiTamuAktif;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class RvmSessionController extends Controller
{
    /**
     * Create a new RVM session token
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rvm_id' => 'required|exists:reverse_vending_machines,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $rvmId = $request->input('rvm_id');
        
        // Generate unique session token
        $sessionToken = Str::uuid()->toString();
        
        // Store session in cache with 10 minutes expiration
        $sessionData = [
            'token' => $sessionToken,
            'rvm_id' => $rvmId,
            'status' => 'menunggu_otorisasi',
            'user_id' => null,
            'created_at' => now(),
            'expires_at' => now()->addMinutes(10)
        ];
        
        Cache::put("rvm_session:{$sessionToken}", $sessionData, 600); // 10 minutes
        
        return response()->json([
            'success' => true,
            'message' => 'Session token created successfully',
            'data' => [
                'session_token' => $sessionToken,
                'rvm_id' => $rvmId,
                'expires_at' => $sessionData['expires_at']->toISOString()
            ]
        ]);
    }

    /**
     * Claim session by authenticated user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function claim(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $sessionToken = $request->input('session_token');
        $user = $request->user(); // From Sanctum middleware
        
        // Retrieve session from cache
        $sessionData = Cache::get("rvm_session:{$sessionToken}");
        
        if (!$sessionData) {
            return response()->json([
                'success' => false,
                'message' => 'Session token not found or expired'
            ], 404);
        }

        if ($sessionData['status'] !== 'menunggu_otorisasi') {
            return response()->json([
                'success' => false,
                'message' => 'Session is no longer available for authorization'
            ], 409);
        }

        // Update session with user info
        $sessionData['status'] = 'diotorisasi';
        $sessionData['user_id'] = $user->id;
        $sessionData['authorized_at'] = now();
        
        Cache::put("rvm_session:{$sessionToken}", $sessionData, 600);
        
        // Broadcast event to RVM
        broadcast(new SesiDiotorisasi($sessionData['rvm_id'], $user->name, $sessionToken));
        
        return response()->json([
            'success' => true,
            'message' => 'Session claimed successfully',
            'data' => [
                'session_token' => $sessionToken,
                'user_name' => $user->name,
                'rvm_id' => $sessionData['rvm_id']
            ]
        ]);
    }

    /**
     * Activate session as guest (donation mode)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function activateGuest(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $sessionToken = $request->input('session_token');
        
        // Retrieve session from cache
        $sessionData = Cache::get("rvm_session:{$sessionToken}");
        
        if (!$sessionData) {
            return response()->json([
                'success' => false,
                'message' => 'Session token not found or expired'
            ], 404);
        }

        if ($sessionData['status'] !== 'menunggu_otorisasi') {
            return response()->json([
                'success' => false,
                'message' => 'Session is no longer available for activation'
            ], 409);
        }

        // Update session for guest mode
        $sessionData['status'] = 'aktif_sebagai_tamu';
        $sessionData['activated_at'] = now();
        
        Cache::put("rvm_session:{$sessionToken}", $sessionData, 600);
        
        // Broadcast event to RVM
        broadcast(new SesiTamuAktif($sessionData['rvm_id'], $sessionToken));
        
        return response()->json([
            'success' => true,
            'message' => 'Guest session activated successfully',
            'data' => [
                'session_token' => $sessionToken,
                'rvm_id' => $sessionData['rvm_id'],
                'mode' => 'guest_donation'
            ]
        ]);
    }

    /**
     * Get session status
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $sessionToken = $request->input('session_token');
        $sessionData = Cache::get("rvm_session:{$sessionToken}");
        
        if (!$sessionData) {
            return response()->json([
                'success' => false,
                'message' => 'Session token not found or expired'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'session_token' => $sessionToken,
                'status' => $sessionData['status'],
                'rvm_id' => $sessionData['rvm_id'],
                'user_id' => $sessionData['user_id'],
                'created_at' => $sessionData['created_at']->toISOString(),
                'expires_at' => $sessionData['expires_at']->toISOString()
            ]
        ]);
    }
}
