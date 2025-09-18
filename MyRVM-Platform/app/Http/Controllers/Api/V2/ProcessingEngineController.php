<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\ProcessingEngine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProcessingEngineController extends Controller
{
    /**
     * List all processing engines
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $engines = ProcessingEngine::with(['rvms'])
                ->when($request->type, function ($query, $type) {
                    return $query->where('type', $type);
                })
                ->when($request->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $engines,
                'message' => 'Processing engines retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving processing engines: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve processing engines',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new processing engine
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:nvidia_cuda,jetson_edge',
            'server_address' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'gpu_memory_limit' => 'nullable|integer',
            'docker_gpu_passthrough' => 'nullable|boolean',
            'model_path' => 'nullable|string',
            'processing_timeout' => 'nullable|integer',
            'auto_failover' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $engine = ProcessingEngine::create([
                'name' => $request->name,
                'type' => $request->type,
                'server_address' => $request->server_address,
                'port' => $request->port,
                'gpu_memory_limit' => $request->gpu_memory_limit,
                'docker_gpu_passthrough' => $request->docker_gpu_passthrough ?? false,
                'model_path' => $request->model_path,
                'processing_timeout' => $request->processing_timeout ?? 30,
                'auto_failover' => $request->auto_failover ?? false,
                'is_active' => $request->is_active ?? true,
                'is_online' => true,
                'last_ping_at' => now(),
            ]);

            Log::info("Processing engine created: {$engine->name} (ID: {$engine->id})");

            return response()->json([
                'success' => true,
                'data' => $engine->load('rvms'),
                'message' => 'Processing engine created successfully'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating processing engine: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create processing engine',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a specific processing engine
     */
    public function show(ProcessingEngine $processingEngine): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $processingEngine->load('rvms'),
                'message' => 'Processing engine retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving processing engine: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve processing engine',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a processing engine
     */
    public function update(Request $request, ProcessingEngine $processingEngine): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|in:nvidia_cuda,jetson_edge',
            'server_address' => 'sometimes|ip',
            'port' => 'sometimes|integer|min:1|max:65535',
            'gpu_memory_limit' => 'nullable|integer',
            'docker_gpu_passthrough' => 'nullable|boolean',
            'model_path' => 'nullable|string',
            'processing_timeout' => 'nullable|integer',
            'auto_failover' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = $request->only([
                'name', 'type', 'server_address', 'port', 'gpu_memory_limit', 
                'docker_gpu_passthrough', 'model_path', 'processing_timeout', 
                'auto_failover', 'is_active'
            ]);

            $processingEngine->update($updateData);

            Log::info("Processing engine updated: {$processingEngine->name} (ID: {$processingEngine->id})");

            return response()->json([
                'success' => true,
                'data' => $processingEngine->load('rvms'),
                'message' => 'Processing engine updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating processing engine: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update processing engine',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a processing engine
     */
    public function destroy(ProcessingEngine $processingEngine): JsonResponse
    {
        try {
            $processingEngine->delete();

            Log::info("Processing engine deleted: {$processingEngine->name} (ID: {$processingEngine->id})");

            return response()->json([
                'success' => true,
                'message' => 'Processing engine deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting processing engine: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete processing engine',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ping a processing engine
     */
    public function ping(ProcessingEngine $processingEngine): JsonResponse
    {
        try {
            $processingEngine->update([
                'last_ping_at' => now(),
                'is_online' => true,
                'is_active' => true
            ]);

            Log::info("Processing engine pinged: {$processingEngine->name} (ID: {$processingEngine->id})");

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $processingEngine->id,
                    'name' => $processingEngine->name,
                    'is_active' => $processingEngine->is_active,
                    'is_online' => $processingEngine->is_online,
                    'last_ping_at' => $processingEngine->last_ping_at,
                    'server_address' => $processingEngine->server_address,
                    'port' => $processingEngine->port
                ],
                'message' => 'Processing engine pinged successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error pinging processing engine: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to ping processing engine',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign processing engine to RVM
     */
    public function assign(Request $request, ProcessingEngine $processingEngine): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rvm_id' => 'required|integer|exists:reverse_vending_machines,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $rvm = \App\Models\ReverseVendingMachine::findOrFail($request->rvm_id);
            
            // Remove existing assignment if any
            $rvm->processingEngines()->detach();
            
            // Assign new processing engine
            $rvm->processingEngines()->attach($processingEngine->id);

            Log::info("Processing engine {$processingEngine->name} assigned to RVM {$rvm->name}");

            return response()->json([
                'success' => true,
                'data' => [
                    'processing_engine' => $processingEngine,
                    'rvm' => $rvm
                ],
                'message' => 'Processing engine assigned to RVM successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error assigning processing engine: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign processing engine',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
