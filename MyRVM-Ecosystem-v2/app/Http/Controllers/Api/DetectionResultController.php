<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetectionResult;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class DetectionResultController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DetectionResult::query();

        if ($request->has('rvm_id')) {
            $query->byRvm($request->rvm_id);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('session_id')) {
            $query->where('session_id', $request->session_id);
        }

        $detections = $query->latest('detected_at')->paginate($request->get('per_page', 15));

        return response()->json(['success' => true, 'data' => $detections]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rvm_id' => 'required|exists:reverse_vending_machines,id',
            'session_id' => 'required|string',
            'user_id' => 'nullable|string',
            'detection_data' => 'required|array',
            'image_path' => 'nullable|string',
            'status' => 'nullable|in:pending,processing,completed,failed',
            'error_message' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $data = $validator->validated();
            if (!isset($data['detected_at'])) {
                $data['detected_at'] = now();
            }
            $detection = DetectionResult::create($data);
            return response()->json(['success' => true, 'data' => $detection], 201);
        } catch (\Exception $e) {
            Log::error('Failed to store detection result: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json(['error' => 'Failed to store detection result'], 500);
        }
    }

    public function show(DetectionResult $detection): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $detection]);
    }

    public function update(Request $request, DetectionResult $detection): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:pending,processing,completed,failed',
            'error_message' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $detection->update($validator->validated());
        return response()->json(['success' => true, 'data' => $detection]);
    }

    public function destroy(DetectionResult $detection): JsonResponse
    {
        $detection->delete();
        return response()->json(['success' => true, 'message' => 'Detection result deleted']);
    }

    public function statistics(): JsonResponse
    {
        $stats = DetectionResult::getGlobalStatistics();
        return response()->json(['success' => true, 'data' => $stats]);
    }

    public function recent(): JsonResponse
    {
        $recent = DetectionResult::latest('detected_at')->limit(10)->get();
        return response()->json(['success' => true, 'data' => $recent]);
    }
}