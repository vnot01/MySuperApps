<?php
/**
 * Example RVM Platform API Endpoints for MyCV-Platform Integration
 * 
 * These endpoints should be implemented in your MyRVM-Platform Laravel application
 * to support the MyCV-Platform integration.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReverseVendingMachine;
use App\Models\DetectionResult;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RvmIntegrationController extends Controller
{
    /**
     * Validate RVM API key
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function validateApiKey(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'api_key' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid request',
                'details' => $validator->errors()
            ], 400);
        }

        $apiKey = $request->input('api_key');
        
        try {
            $rvm = ReverseVendingMachine::where('api_key', $apiKey)
                ->where('status', '!=', 'inactive')
                ->first();

            if (!$rvm) {
                return response()->json([
                    'valid' => false,
                    'error' => 'Invalid API key or RVM inactive'
                ], 401);
            }

            return response()->json([
                'valid' => true,
                'rvm' => [
                    'id' => $rvm->id,
                    'name' => $rvm->name,
                    'location_description' => $rvm->location_description,
                    'status' => $rvm->status,
                    'api_key' => $rvm->api_key
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('RVM API key validation error', [
                'error' => $e->getMessage(),
                'api_key' => $apiKey
            ]);

            return response()->json([
                'valid' => false,
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get RVM information by ID
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function getRvm(int $id): JsonResponse
    {
        try {
            $rvm = ReverseVendingMachine::find($id);

            if (!$rvm) {
                return response()->json([
                    'error' => 'RVM not found'
                ], 404);
            }

            return response()->json([
                'id' => $rvm->id,
                'name' => $rvm->name,
                'location_description' => $rvm->location_description,
                'status' => $rvm->status,
                'api_key' => $rvm->api_key,
                'created_at' => $rvm->created_at,
                'updated_at' => $rvm->updated_at
            ]);

        } catch (\Exception $e) {
            Log::error('Get RVM error', [
                'error' => $e->getMessage(),
                'rvm_id' => $id
            ]);

            return response()->json([
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Store detection results from MyCV-Platform
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function storeDetection(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rvm_id' => 'required|integer|exists:reverse_vending_machines,id',
            'session_id' => 'required|string',
            'detection_data' => 'required|array',
            'image_path' => 'nullable|string',
            'detected_at' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid request',
                'details' => $validator->errors()
            ], 400);
        }

        try {
            // Verify RVM exists and is active
            $rvm = ReverseVendingMachine::find($request->input('rvm_id'));
            if (!$rvm || $rvm->status === 'inactive') {
                return response()->json([
                    'error' => 'RVM not found or inactive'
                ], 404);
            }

            // Create detection result record
            $detectionResult = DetectionResult::create([
                'rvm_id' => $request->input('rvm_id'),
                'session_id' => $request->input('session_id'),
                'detection_data' => $request->input('detection_data'),
                'image_path' => $request->input('image_path'),
                'detected_at' => $request->input('detected_at'),
                'status' => 'completed'
            ]);

            // Update RVM last activity
            $rvm->update([
                'last_api_access' => now()
            ]);

            return response()->json([
                'success' => true,
                'detection_id' => $detectionResult->id,
                'message' => 'Detection result stored successfully'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Store detection error', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Failed to store detection result'
            ], 500);
        }
    }

    /**
     * Get RVM statistics
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function getRvmStats(int $id): JsonResponse
    {
        try {
            $rvm = ReverseVendingMachine::find($id);

            if (!$rvm) {
                return response()->json([
                    'error' => 'RVM not found'
                ], 404);
            }

            // Get statistics
            $stats = [
                'rvm_id' => $rvm->id,
                'name' => $rvm->name,
                'status' => $rvm->status,
                'total_detections' => DetectionResult::where('rvm_id', $id)->count(),
                'detections_today' => DetectionResult::where('rvm_id', $id)
                    ->whereDate('created_at', today())
                    ->count(),
                'detections_this_week' => DetectionResult::where('rvm_id', $id)
                    ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                'last_detection' => DetectionResult::where('rvm_id', $id)
                    ->latest()
                    ->first()?->created_at,
                'created_at' => $rvm->created_at,
                'last_api_access' => $rvm->last_api_access
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Get RVM stats error', [
                'error' => $e->getMessage(),
                'rvm_id' => $id
            ]);

            return response()->json([
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get RVM detection results with pagination
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getRvmDetections(Request $request, int $id): JsonResponse
    {
        try {
            $rvm = ReverseVendingMachine::find($id);

            if (!$rvm) {
                return response()->json([
                    'error' => 'RVM not found'
                ], 404);
            }

            $perPage = $request->input('per_page', 20);
            $page = $request->input('page', 1);

            $detections = DetectionResult::where('rvm_id', $id)
                ->with('rvm:id,name,location_description')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'data' => $detections->items(),
                'pagination' => [
                    'current_page' => $detections->currentPage(),
                    'last_page' => $detections->lastPage(),
                    'per_page' => $detections->perPage(),
                    'total' => $detections->total(),
                    'has_more' => $detections->hasMorePages()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get RVM detections error', [
                'error' => $e->getMessage(),
                'rvm_id' => $id
            ]);

            return response()->json([
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update RVM status
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateRvmStatus(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive,maintenance,full,error',
            'api_key' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid request',
                'details' => $validator->errors()
            ], 400);
        }

        try {
            $rvm = ReverseVendingMachine::where('id', $id)
                ->where('api_key', $request->input('api_key'))
                ->first();

            if (!$rvm) {
                return response()->json([
                    'error' => 'RVM not found or invalid API key'
                ], 404);
            }

            $oldStatus = $rvm->status;
            $rvm->update([
                'status' => $request->input('status'),
                'last_status_change' => now(),
                'last_api_access' => now()
            ]);

            Log::info('RVM status updated', [
                'rvm_id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $request->input('status')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'RVM status updated successfully',
                'rvm' => [
                    'id' => $rvm->id,
                    'name' => $rvm->name,
                    'status' => $rvm->status,
                    'last_status_change' => $rvm->last_status_change
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Update RVM status error', [
                'error' => $e->getMessage(),
                'rvm_id' => $id
            ]);

            return response()->json([
                'error' => 'Failed to update RVM status'
            ], 500);
        }
    }
}
