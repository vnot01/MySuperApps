<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\ReverseVendingMachine;
use App\Models\RvmSession;
use App\Models\User;
use App\Events\SessionAuthorized;
use App\Events\SessionGuestActivated;
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
        
        // Create session in database
        $session = RvmSession::create([
            'id' => Str::uuid()->toString(),
            'rvm_id' => $rvmId,
            'session_token' => $sessionToken,
            'status' => 'active',
            'expires_at' => now()->addMinutes(10)
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Session token created successfully',
            'data' => [
                'id' => $session->id,
                'session_token' => $sessionToken,
                'rvm_id' => $rvmId,
                'expires_at' => $session->expires_at->toISOString()
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
        
        // Find session in database
        $session = RvmSession::where('session_token', $sessionToken)->first();
        
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session token not found or expired'
            ], 404);
        }

        if ($session->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Session is no longer available for authorization'
            ], 409);
        }

        // Update session with user info
        $session->update([
            'status' => 'claimed',
            'user_id' => $user->id,
            'claimed_at' => now()
        ]);
        
        // Broadcast event to RVM
        broadcast(new SessionAuthorized($session->rvm_id, $session->id, $user->id, $user->name));
        
        return response()->json([
            'success' => true,
            'message' => 'Session claimed successfully',
            'data' => [
                'session_id' => $session->id,
                'session_token' => $sessionToken,
                'user_name' => $user->name,
                'rvm_id' => $session->rvm_id
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
        
        // Find session in database
        $session = RvmSession::where('session_token', $sessionToken)->first();
        
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session token not found or expired'
            ], 404);
        }

        if ($session->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Session is no longer available for activation'
            ], 409);
        }

        // Update session for guest mode
        $session->update([
            'status' => 'claimed',
            'claimed_at' => now()
        ]);
        
        // Broadcast event to RVM
        broadcast(new SessionGuestActivated($session->rvm_id, $session->id));
        
        return response()->json([
            'success' => true,
            'message' => 'Guest session activated successfully',
            'data' => [
                'session_id' => $session->id,
                'session_token' => $sessionToken,
                'rvm_id' => $session->rvm_id,
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
        
        // Find session in database
        $session = RvmSession::where('session_token', $sessionToken)->first();
        
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session token not found or expired'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'session_token' => $sessionToken,
                'status' => $session->status,
                'rvm_id' => $session->rvm_id,
                'user_id' => $session->user_id,
                'created_at' => $session->created_at->toISOString(),
                'expires_at' => $session->expires_at->toISOString()
            ]
        ]);
    }
}
