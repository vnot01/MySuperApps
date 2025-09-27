<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\DetectionResult;
use App\Models\ReverseVendingMachine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DetectionResultController extends Controller
{
    /**
     * List detection results
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $results = DetectionResult::with(['reverseVendingMachine'])
                ->when($request->rvm_id, function ($query, $rvmId) {
                    return $query->where('rvm_id', $rvmId);
                })
                ->when($request->date_from, function ($query, $dateFrom) {
                    return $query->whereDate('created_at', '>=', $dateFrom);
                })
                ->when($request->date_to, function ($query, $dateTo) {
                    return $query->whereDate('created_at', '<=', $dateTo);
                })
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $results,
                'message' => 'Detection results retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving detection results: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve detection results',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new detection result
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rvm_id' => 'required|integer|exists:reverse_vending_machines,id',
            'image_path' => 'required|string',
            'detections' => 'required|array',
            'detections.*.class' => 'required|string',
            'detections.*.confidence' => 'required|numeric|between:0,1',
            'detections.*.bbox' => 'required|array|size:4',
            'detections.*.bbox.*' => 'numeric',
            'detections.*.segmentation_mask' => 'nullable|string',
            'timestamp' => 'nullable|date',
            'processing_time' => 'nullable|numeric',
            'model_version' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $detectionResult = DetectionResult::create([
                'rvm_id' => $request->rvm_id,
                'image_path' => $request->image_path,
                'detections' => json_encode($request->detections),
                'timestamp' => $request->timestamp ?: now(),
                'processing_time' => $request->processing_time,
                'model_version' => $request->model_version,
                'status' => 'completed'
            ]);

            // Update RVM last activity
            $rvm = ReverseVendingMachine::find($request->rvm_id);
            if ($rvm) {
                $rvm->update(['updated_at' => now()]);
            }

            Log::info("Detection result created for RVM {$request->rvm_id}: " . count($request->detections) . " detections");

            return response()->json([
                'success' => true,
                'data' => $detectionResult->load('reverseVendingMachine'),
                'message' => 'Detection result stored successfully'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating detection result: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to store detection result',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a specific detection result
     */
    public function show(DetectionResult $detectionResult): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $detectionResult->load('reverseVendingMachine'),
                'message' => 'Detection result retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving detection result: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve detection result',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get RVM status
     */
    public function getRvmStatus(Request $request, int $rvmId): JsonResponse
    {
        try {
            $rvm = ReverseVendingMachine::with(['processingEngines', 'deposits' => function ($query) {
                $query->latest()->limit(5);
            }])->findOrFail($rvmId);

            // Get latest detection result
            $latestDetection = DetectionResult::where('rvm_id', $rvmId)
                ->latest()
                ->first();

            // Get processing statistics
            $detectionStats = DetectionResult::where('rvm_id', $rvmId)
                ->selectRaw('
                    COUNT(*) as total_detections,
                    AVG(processing_time) as avg_processing_time,
                    MAX(created_at) as last_detection_at
                ')
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'rvm' => $rvm,
                    'latest_detection' => $latestDetection,
                    'detection_stats' => $detectionStats,
                    'status' => [
                        'is_online' => $rvm->status === 'active',
                        'last_activity' => $rvm->updated_at,
                        'processing_engines_count' => $rvm->processingEngines->count(),
                        'recent_deposits_count' => $rvm->deposits->count()
                    ]
                ],
                'message' => 'RVM status retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving RVM status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve RVM status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Trigger processing for RVM
     */
    public function triggerProcessing(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rvm_id' => 'required|integer|exists:reverse_vending_machines,id',
            'processing_type' => 'nullable|string|in:detection,segmentation,full_analysis',
            'priority' => 'nullable|string|in:low,normal,high'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $rvm = ReverseVendingMachine::findOrFail($request->rvm_id);
            
            // Check if RVM has processing engines assigned
            if ($rvm->processingEngines->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No processing engines assigned to this RVM'
                ], 400);
            }

            // Create processing request record
            $processingRequest = DetectionResult::create([
                'rvm_id' => $request->rvm_id,
                'image_path' => null,
                'detections' => json_encode([]),
                'timestamp' => now(),
                'processing_time' => null,
                'model_version' => null,
                'status' => 'processing_requested',
                'processing_type' => $request->processing_type ?: 'detection',
                'priority' => $request->priority ?: 'normal'
            ]);

            Log::info("Processing triggered for RVM {$rvm->name} (ID: {$rvm->id})");

            return response()->json([
                'success' => true,
                'data' => [
                    'processing_request_id' => $processingRequest->id,
                    'rvm' => $rvm,
                    'processing_engines' => $rvm->processingEngines,
                    'status' => 'processing_requested'
                ],
                'message' => 'Processing triggered successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error triggering processing: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to trigger processing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get processing history
     */
    public function getProcessingHistory(Request $request): JsonResponse
    {
        try {
            $query = DetectionResult::with(['reverseVendingMachine'])
                ->when($request->rvm_id, function ($query, $rvmId) {
                    return $query->where('rvm_id', $rvmId);
                })
                ->when($request->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($request->date_from, function ($query, $dateFrom) {
                    return $query->whereDate('created_at', '>=', $dateFrom);
                })
                ->when($request->date_to, function ($query, $dateTo) {
                    return $query->whereDate('created_at', '<=', $dateTo);
                })
                ->orderBy('created_at', 'desc');

            $limit = $request->get('limit', 10);
            $results = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'data' => $results,
                'message' => 'Processing history retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving processing history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve processing history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload image file
     */
    public function uploadImageFile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
            'rvm_id' => 'required|integer|exists:reverse_vending_machines,id',
            'metadata' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('image');
            $rvmId = $request->rvm_id;
            
            // Generate unique filename
            $filename = 'detection_' . $rvmId . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Store file in storage/app/public/detection-images
            $path = $file->storeAs('detection-images', $filename, 'public');
            
            // Get full URL
            $url = Storage::url($path);

            Log::info("Image uploaded for RVM {$rvmId}: {$filename}");

            return response()->json([
                'success' => true,
                'data' => [
                    'filename' => $filename,
                    'path' => $path,
                    'url' => $url,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'rvm_id' => $rvmId
                ],
                'message' => 'Image uploaded successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading image: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
