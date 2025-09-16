<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use App\Models\ReverseVendingMachine;
use App\Services\CacheService;
use Carbon\Carbon;

class EdgeVisionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware('role:admin|super_admin'); // Commented out for testing
    }

    /**
     * Display Edge Vision Dashboard
     */
    public function index(): View
    {
        $rvms = ReverseVendingMachine::getCachedAdminList();
        
        return view('admin.edge-vision.index', compact('rvms'));
    }

    /**
     * Get Edge Vision Statistics
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $stats = CacheService::remember('edge_vision', 'statistics', function () {
                return [
                    'total_rvms' => ReverseVendingMachine::count(),
                    'active_rvms' => ReverseVendingMachine::where('status', 'active')->count(),
                    'cv_processing_today' => $this->getCvProcessingToday(),
                    'cv_success_rate' => $this->getCvSuccessRate(),
                    'last_processing' => $this->getLastProcessingTime(),
                    'pending_uploads' => $this->getPendingUploads(),
                ];
            }, 300); // 5 minutes cache

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get RVM Edge Vision Status
     */
    public function getRvmStatus(Request $request): JsonResponse
    {
        try {
            $rvmId = $request->get('rvm_id');
            
            if (!$rvmId) {
                return response()->json([
                    'success' => false,
                    'message' => 'RVM ID is required'
                ], 400);
            }

            $rvm = ReverseVendingMachine::findOrFail($rvmId);
            
            $status = CacheService::remember('edge_vision', "rvm_status_{$rvmId}", function () use ($rvm) {
                return [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'cv_enabled' => $rvm->cv_enabled ?? false,
                    'last_cv_processing' => $this->getLastCvProcessing($rvm->id),
                    'cv_processing_count' => $this->getCvProcessingCount($rvm->id),
                    'cv_success_rate' => $this->getRvmCvSuccessRate($rvm->id),
                    'pending_uploads' => $this->getRvmPendingUploads($rvm->id),
                    'model_status' => $this->getModelStatus($rvm->id),
                ];
            }, 60); // 1 minute cache

            return response()->json([
                'success' => true,
                'data' => $status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch RVM status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Trigger CV Processing
     */
    public function triggerProcessing(Request $request): JsonResponse
    {
        try {
            $rvmId = $request->get('rvm_id');
            $processingType = $request->get('type', 'yolo'); // yolo, sam2, both
            
            if (!$rvmId) {
                return response()->json([
                    'success' => false,
                    'message' => 'RVM ID is required'
                ], 400);
            }

            $rvm = ReverseVendingMachine::findOrFail($rvmId);
            
            // Simulate CV processing trigger
            $result = $this->simulateCvProcessing($rvmId, $processingType);
            
            return response()->json([
                'success' => true,
                'message' => 'CV processing triggered successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to trigger CV processing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get CV Processing History
     */
    public function getProcessingHistory(Request $request): JsonResponse
    {
        try {
            $rvmId = $request->get('rvm_id');
            $limit = $request->get('limit', 10);
            
            $history = CacheService::remember('edge_vision', "history_{$rvmId}_{$limit}", function () use ($rvmId, $limit) {
                return $this->getCvProcessingHistory($rvmId, $limit);
            }, 300); // 5 minutes cache

            return response()->json([
                'success' => true,
                'data' => $history
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch processing history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload CV Results
     */
    public function uploadResults(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'rvm_id' => 'required|integer',
                'image_path' => 'required|string',
                'results' => 'required|array',
                'processing_type' => 'required|string|in:yolo,sam2,both',
                'timestamp' => 'required|date',
            ]);

            // Simulate upload to MinIO/S3
            $uploadResult = $this->simulateUploadToStorage($data);
            
            return response()->json([
                'success' => true,
                'message' => 'CV results uploaded successfully',
                'data' => $uploadResult
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload results: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Model Status
     */
    public function getModelStatus($rvmId = null): array
    {
        // Simulate model status
        return [
            'yolo11_model' => [
                'status' => 'loaded',
                'version' => '11.0.0',
                'size' => '6.2MB',
                'last_updated' => Carbon::now()->subHours(2)->toISOString(),
            ],
            'sam2_model' => [
                'status' => 'loaded',
                'version' => '2.1.0',
                'size' => '375MB',
                'last_updated' => Carbon::now()->subHours(1)->toISOString(),
            ],
        ];
    }

    /**
     * Simulate CV Processing
     */
    private function simulateCvProcessing($rvmId, $type): array
    {
        return [
            'processing_id' => uniqid('cv_'),
            'rvm_id' => $rvmId,
            'type' => $type,
            'status' => 'processing',
            'started_at' => Carbon::now()->toISOString(),
            'estimated_duration' => $type === 'both' ? 30 : 15, // seconds
        ];
    }

    /**
     * Simulate Upload to Storage
     */
    private function simulateUploadToStorage($data): array
    {
        return [
            'upload_id' => uniqid('upload_'),
            'storage_path' => "cv-results/{$data['rvm_id']}/" . date('Y/m/d') . "/" . basename($data['image_path']),
            'uploaded_at' => Carbon::now()->toISOString(),
            'file_size' => rand(500, 2000) . 'KB',
        ];
    }

    /**
     * Get CV Processing Today
     */
    private function getCvProcessingToday(): int
    {
        // Simulate data
        return rand(50, 200);
    }

    /**
     * Get CV Success Rate
     */
    private function getCvSuccessRate(): float
    {
        // Simulate data
        return round(rand(85, 98) + (rand(0, 99) / 100), 2);
    }

    /**
     * Get Last Processing Time
     */
    private function getLastProcessingTime(): ?string
    {
        // Simulate data
        return Carbon::now()->subMinutes(rand(5, 60))->toISOString();
    }

    /**
     * Get Pending Uploads
     */
    private function getPendingUploads(): int
    {
        // Simulate data
        return rand(0, 10);
    }

    /**
     * Get Last CV Processing for RVM
     */
    private function getLastCvProcessing($rvmId): ?string
    {
        // Simulate data
        return Carbon::now()->subMinutes(rand(10, 120))->toISOString();
    }

    /**
     * Get CV Processing Count for RVM
     */
    private function getCvProcessingCount($rvmId): int
    {
        // Simulate data
        return rand(100, 500);
    }

    /**
     * Get RVM CV Success Rate
     */
    private function getRvmCvSuccessRate($rvmId): float
    {
        // Simulate data
        return round(rand(80, 95) + (rand(0, 99) / 100), 2);
    }

    /**
     * Get RVM Pending Uploads
     */
    private function getRvmPendingUploads($rvmId): int
    {
        // Simulate data
        return rand(0, 5);
    }

    /**
     * Get CV Processing History
     */
    private function getCvProcessingHistory($rvmId, $limit): array
    {
        $history = [];
        
        for ($i = 0; $i < $limit; $i++) {
            $history[] = [
                'id' => uniqid('cv_'),
                'rvm_id' => $rvmId,
                'type' => ['yolo', 'sam2', 'both'][rand(0, 2)],
                'status' => ['completed', 'processing', 'failed'][rand(0, 2)],
                'started_at' => Carbon::now()->subMinutes(rand(1, 1440))->toISOString(),
                'completed_at' => Carbon::now()->subMinutes(rand(1, 1440))->toISOString(),
                'processing_time' => rand(5, 45), // seconds
                'objects_detected' => rand(0, 10),
                'confidence' => round(rand(70, 99) + (rand(0, 99) / 100), 2),
            ];
        }
        
        return $history;
    }
}
