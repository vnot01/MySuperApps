<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReverseVendingMachine;
use App\Models\DetectionResult;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RvmIntegrationController extends Controller
{
    public function validateApiKey(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'api_key' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $rvm = ReverseVendingMachine::where('api_key', $request->api_key)
            ->where('status', 'active')
            ->first();

        if (!$rvm || !$rvm->isApiKeyValid()) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        // Update last API access
        $rvm->update(['last_api_access' => now()]);

        return response()->json([
            'valid' => true,
            'rvm_id' => $rvm->id,
            'rvm_name' => $rvm->name,
            'status' => $rvm->status
        ]);
    }

    public function getRvm(int $id): JsonResponse
    {
        $rvm = ReverseVendingMachine::find($id);
        
        if (!$rvm) {
            return response()->json(['error' => 'RVM not found'], 404);
        }

        return response()->json([
            'id' => $rvm->id,
            'name' => $rvm->name,
            'location' => $rvm->location,
            'ip_address' => $rvm->ip_address,
            'status' => $rvm->status,
            'capacity' => $rvm->capacity,
            'current_load' => $rvm->current_load,
            'last_online_at' => $rvm->last_ping,
            'api_key_valid' => $rvm->isApiKeyValid()
        ]);
    }

    public function storeDetection(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rvm_id' => 'required|exists:reverse_vending_machines,id',
            'session_id' => 'required|string',
            'detection_data' => 'required|array',
            'image_path' => 'nullable|string',
            'user_id' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $detection = DetectionResult::create([
                'rvm_id' => $request->rvm_id,
                'session_id' => $request->session_id,
                'user_id' => $request->user_id,
                'detection_data' => $request->detection_data,
                'image_path' => $request->image_path,
                'detected_at' => now(),
                'status' => 'completed'
            ]);

            // Update RVM last online
            ReverseVendingMachine::find($request->rvm_id)
                ->update(['last_ping' => now()]);

            return response()->json([
                'success' => true,
                'detection_id' => $detection->id
            ]);

        } catch (\Exception $e) {
            Log::error('Detection storage failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json(['error' => 'Storage failed'], 500);
        }
    }

    public function getRvmStats(int $id): JsonResponse
    {
        $rvm = ReverseVendingMachine::find($id);
        
        if (!$rvm) {
            return response()->json(['error' => 'RVM not found'], 404);
        }

        $stats = Cache::remember("rvm_stats_{$id}", 300, function () use ($id) {
            return DetectionResult::getRvmStatistics($id);
        });

        return response()->json($stats);
    }

    public function getRvmDetections(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:pending,processing,completed,failed',
            'limit' => 'nullable|integer|min:1|max:100',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $query = DetectionResult::byRvm($id);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date_from) {
            $query->whereDate('detected_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('detected_at', '<=', $request->date_to);
        }

        $detections = $query->latest('detected_at')
            ->limit($request->limit ?? 50)
            ->get();

        return response()->json($detections);
    }

    public function updateRvmStatus(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive,maintenance,error',
            'current_load' => 'nullable|integer|min:0',
            'metrics' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $rvm = ReverseVendingMachine::find($id);
        
        if (!$rvm) {
            return response()->json(['error' => 'RVM not found'], 404);
        }

        $updateData = ['status' => $request->status];

        if ($request->current_load !== null) {
            $updateData['current_load'] = $request->current_load;
        }

        if ($request->metrics) {
            $updateData['metrics'] = $request->metrics;
        }

        $rvm->update($updateData);

        return response()->json(['success' => true]);
    }
}