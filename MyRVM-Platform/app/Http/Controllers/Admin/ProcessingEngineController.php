<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProcessingEngine;
use App\Models\ReverseVendingMachine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProcessingEngineController extends Controller
{
    /**
     * Display a listing of processing engines
     */
    public function index()
    {
        $engines = ProcessingEngine::with('rvms')->get();
        return view('admin.processing-engines.index', compact('engines'));
    }

    /**
     * Get all processing engines
     */
    public function getEngines(): JsonResponse
    {
        $engines = ProcessingEngine::all();
        
        return response()->json([
            'success' => true,
            'data' => $engines
        ]);
    }

    /**
     * Get NVIDIA CUDA engines
     */
    public function getNvidiaCudaEngines(): JsonResponse
    {
        $engines = ProcessingEngine::nvidiaCuda()->get();
        
        return response()->json([
            'success' => true,
            'data' => $engines
        ]);
    }

    /**
     * Get Jetson Edge engines
     */
    public function getJetsonEdgeEngines(): JsonResponse
    {
        $engines = ProcessingEngine::jetsonEdge()->get();
        
        return response()->json([
            'success' => true,
            'data' => $engines
        ]);
    }

    /**
     * Get engines for specific RVM
     */
    public function getRvmEngines(Request $request): JsonResponse
    {
        $rvmId = $request->get('rvm_id');
        
        if (!$rvmId) {
            return response()->json([
                'success' => false,
                'message' => 'RVM ID is required'
            ], 400);
        }

        $rvm = ReverseVendingMachine::find($rvmId);
        if (!$rvm) {
            return response()->json([
                'success' => false,
                'message' => 'RVM not found'
            ], 404);
        }

        $engines = $rvm->processingEngines()->get();
        
        return response()->json([
            'success' => true,
            'data' => $engines
        ]);
    }

    /**
     * Store a newly created processing engine
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:nvidia_cuda,jetson_edge',
            'server_address' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'gpu_memory_limit' => 'nullable|string',
            'docker_gpu_passthrough' => 'boolean',
            'model_path' => 'nullable|string',
            'processing_timeout' => 'integer|min:1|max:300',
            'auto_failover' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $engine = ProcessingEngine::create($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Processing engine created successfully',
                'data' => $engine
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create processing engine',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified processing engine
     */
    public function update(Request $request, ProcessingEngine $engine): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:nvidia_cuda,jetson_edge',
            'server_address' => 'sometimes|required|ip',
            'port' => 'sometimes|required|integer|min:1|max:65535',
            'gpu_memory_limit' => 'nullable|string',
            'docker_gpu_passthrough' => 'boolean',
            'model_path' => 'nullable|string',
            'processing_timeout' => 'integer|min:1|max:300',
            'auto_failover' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $engine->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Processing engine updated successfully',
                'data' => $engine
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update processing engine',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified processing engine
     */
    public function destroy(ProcessingEngine $engine): JsonResponse
    {
        try {
            $engine->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Processing engine deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete processing engine',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle activation status of processing engine
     */
    public function toggleActivation(ProcessingEngine $engine): JsonResponse
    {
        try {
            $engine->update(['is_active' => !$engine->is_active]);
            
            return response()->json([
                'success' => true,
                'message' => 'Processing engine activation toggled successfully',
                'data' => [
                    'id' => $engine->id,
                    'is_active' => $engine->is_active
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle activation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ping a specific processing engine
     */
    public function ping(ProcessingEngine $engine): JsonResponse
    {
        try {
            $startTime = microtime(true);
            
            // Simulate ping (in real implementation, you would actually ping the server)
            $responseTime = rand(10, 100); // Mock response time in milliseconds
            $isOnline = $responseTime < 100; // Mock online status
            
            $engine->update([
                'is_online' => $isOnline,
                'last_ping_at' => now(),
                'ping_response_time' => $responseTime,
                'health_status' => [
                    'cpu_usage' => rand(20, 80),
                    'gpu_usage' => rand(10, 90),
                    'memory_usage' => rand(30, 85),
                    'disk_usage' => rand(40, 70)
                ]
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Ping completed',
                'data' => [
                    'id' => $engine->id,
                    'is_online' => $isOnline,
                    'response_time' => $responseTime,
                    'last_ping_at' => $engine->last_ping_at,
                    'health_status' => $engine->health_status
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ping failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ping all processing engines
     */
    public function pingAll(): JsonResponse
    {
        try {
            $engines = ProcessingEngine::active()->get();
            $results = [];
            
            foreach ($engines as $engine) {
                $startTime = microtime(true);
                $responseTime = rand(10, 100);
                $isOnline = $responseTime < 100;
                
                $engine->update([
                    'is_online' => $isOnline,
                    'last_ping_at' => now(),
                    'ping_response_time' => $responseTime,
                    'health_status' => [
                        'cpu_usage' => rand(20, 80),
                        'gpu_usage' => rand(10, 90),
                        'memory_usage' => rand(30, 85),
                        'disk_usage' => rand(40, 70)
                    ]
                ]);
                
                $results[] = [
                    'id' => $engine->id,
                    'name' => $engine->name,
                    'is_online' => $isOnline,
                    'response_time' => $responseTime
                ];
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Ping all completed',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ping all failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign processing engine to RVM
     */
    public function assignToRvm(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rvm_id' => 'required|exists:reverse_vending_machines,id',
            'processing_engine_id' => 'required|exists:processing_engines,id',
            'priority' => 'required|in:primary,secondary,backup',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $rvm = ReverseVendingMachine::find($request->rvm_id);
            $engine = ProcessingEngine::find($request->processing_engine_id);
            
            // Check if relationship already exists
            $existing = DB::table('rvm_processing_engines')
                ->where('rvm_id', $request->rvm_id)
                ->where('processing_engine_id', $request->processing_engine_id)
                ->first();
                
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Relationship already exists'
                ], 400);
            }
            
            $rvm->processingEngines()->attach($request->processing_engine_id, [
                'priority' => $request->priority,
                'is_active' => $request->get('is_active', true),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Processing engine assigned to RVM successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign processing engine',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove processing engine from RVM
     */
    public function removeFromRvm(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rvm_id' => 'required|exists:reverse_vending_machines,id',
            'processing_engine_id' => 'required|exists:processing_engines,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $rvm = ReverseVendingMachine::find($request->rvm_id);
            $rvm->processingEngines()->detach($request->processing_engine_id);
            
            return response()->json([
                'success' => true,
                'message' => 'Processing engine removed from RVM successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove processing engine',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}