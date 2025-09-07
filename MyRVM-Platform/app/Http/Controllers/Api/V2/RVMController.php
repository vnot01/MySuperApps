<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\ReverseVendingMachine;
use App\Models\Deposit;
use App\Models\RvmSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RVMController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        // TODO: Add role middleware when available
        // $this->middleware('role:admin|superadmin|tenant');
    }

    /**
     * Get all RVMs with pagination and filtering
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getRVMs(Request $request): JsonResponse
    {
        try {
            $query = ReverseVendingMachine::query();

            // Apply filters
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('location_description', 'like', "%{$search}%");
                });
            }

            if ($request->has('status')) {
                $status = $request->input('status');
                $query->where('status', $status);
            }

            // Apply sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = min($request->input('per_page', 15), 100);
            $rvms = $query->paginate($perPage);

            // Transform data
            $rvms->getCollection()->transform(function ($rvm) {
                return [
                    'id' => $rvm->id,
                    'name' => $rvm->name,
                    'location_description' => $rvm->location_description,
                    'status' => $rvm->status,
                    'api_key' => $rvm->api_key,
                    'deposits_count' => $rvm->deposits()->count(),
                    'sessions_count' => $rvm->sessions()->count(),
                    'active_sessions_count' => $rvm->sessions()->where('status', 'active')->count(),
                    'created_at' => $rvm->created_at,
                    'updated_at' => $rvm->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'RVMs retrieved successfully',
                'data' => $rvms->items(),
                'pagination' => [
                    'current_page' => $rvms->currentPage(),
                    'per_page' => $rvms->perPage(),
                    'total' => $rvms->total(),
                    'last_page' => $rvms->lastPage(),
                    'from' => $rvms->firstItem(),
                    'to' => $rvms->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve RVMs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get RVM details by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getRVM(int $id): JsonResponse
    {
        try {
            $rvm = ReverseVendingMachine::with(['deposits', 'sessions'])->findOrFail($id);

            $data = [
                'id' => $rvm->id,
                'name' => $rvm->name,
                'location_description' => $rvm->location_description,
                'status' => $rvm->status,
                'api_key' => $rvm->api_key,
                'statistics' => [
                    'deposits_count' => $rvm->deposits()->count(),
                    'sessions_count' => $rvm->sessions()->count(),
                    'active_sessions_count' => $rvm->sessions()->where('status', 'active')->count(),
                    'completed_deposits' => $rvm->deposits()->where('status', 'completed')->count(),
                    'pending_deposits' => $rvm->deposits()->where('status', 'pending')->count(),
                    'total_rewards_given' => $rvm->deposits()->where('status', 'completed')->sum('reward_amount'),
                ],
                'recent_deposits' => $rvm->deposits()
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($deposit) {
                        return [
                            'id' => $deposit->id,
                            'user_id' => $deposit->user_id,
                            'status' => $deposit->status,
                            'reward_amount' => $deposit->reward_amount,
                            'cv_confidence' => $deposit->cv_confidence,
                            'cv_waste_type' => $deposit->cv_waste_type,
                            'created_at' => $deposit->created_at,
                        ];
                    }),
                'recent_sessions' => $rvm->sessions()
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($session) {
                        return [
                            'id' => $session->id,
                            'user_id' => $session->user_id,
                            'status' => $session->status,
                            'created_at' => $session->created_at,
                            'expires_at' => $session->expires_at,
                        ];
                    }),
                'created_at' => $rvm->created_at,
                'updated_at' => $rvm->updated_at,
            ];

            return response()->json([
                'success' => true,
                'message' => 'RVM details retrieved successfully',
                'data' => $data,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve RVM details',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new RVM
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createRVM(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:reverse_vending_machines,name',
                'location_description' => 'nullable|string|max:1000',
                'status' => 'required|in:active,inactive,maintenance,full',
                'api_key' => 'nullable|string|max:255|unique:reverse_vending_machines,api_key',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Generate API key if not provided
            $apiKey = $request->input('api_key', 'rvm_' . Str::random(32));

            $rvm = ReverseVendingMachine::create([
                'name' => $request->name,
                'location_description' => $request->location_description,
                'status' => $request->status,
                'api_key' => $apiKey,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'RVM created successfully',
                'data' => [
                    'id' => $rvm->id,
                    'name' => $rvm->name,
                    'location_description' => $rvm->location_description,
                    'status' => $rvm->status,
                    'api_key' => $rvm->api_key,
                    'created_at' => $rvm->created_at,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create RVM',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an RVM
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateRVM(Request $request, int $id): JsonResponse
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255|unique:reverse_vending_machines,name,' . $id,
                'location_description' => 'nullable|string|max:1000',
                'status' => 'sometimes|required|in:active,inactive,maintenance,full',
                'api_key' => 'nullable|string|max:255|unique:reverse_vending_machines,api_key,' . $id,
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $updateData = $request->only(['name', 'location_description', 'status', 'api_key']);
            $rvm->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'RVM updated successfully',
                'data' => [
                    'id' => $rvm->id,
                    'name' => $rvm->name,
                    'location_description' => $rvm->location_description,
                    'status' => $rvm->status,
                    'api_key' => $rvm->api_key,
                    'updated_at' => $rvm->updated_at,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update RVM',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an RVM
     *
     * @param int $id
     * @return JsonResponse
     */
    public function deleteRVM(int $id): JsonResponse
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);

            // Check if RVM has associated data
            $depositsCount = $rvm->deposits()->count();
            $sessionsCount = $rvm->sessions()->count();

            if ($depositsCount > 0 || $sessionsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete RVM with associated data',
                    'data' => [
                        'deposits_count' => $depositsCount,
                        'sessions_count' => $sessionsCount,
                    ],
                ], 409);
            }

            $rvm->delete();

            return response()->json([
                'success' => true,
                'message' => 'RVM deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete RVM',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get RVM statistics
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getRVMStatistics(int $id): JsonResponse
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);

            $statistics = [
                'rvm_info' => [
                    'id' => $rvm->id,
                    'name' => $rvm->name,
                    'status' => $rvm->status,
                ],
                'deposits' => [
                    'total' => $rvm->deposits()->count(),
                    'completed' => $rvm->deposits()->where('status', 'completed')->count(),
                    'pending' => $rvm->deposits()->where('status', 'pending')->count(),
                    'rejected' => $rvm->deposits()->where('status', 'rejected')->count(),
                    'total_rewards_given' => $rvm->deposits()->where('status', 'completed')->sum('reward_amount'),
                    'avg_confidence' => $rvm->deposits()->where('status', 'completed')->avg('cv_confidence'),
                ],
                'sessions' => [
                    'total' => $rvm->sessions()->count(),
                    'active' => $rvm->sessions()->where('status', 'active')->count(),
                    'completed' => $rvm->sessions()->where('status', 'completed')->count(),
                    'expired' => $rvm->sessions()->where('status', 'expired')->count(),
                ],
                'waste_types' => [
                    'by_type' => $rvm->deposits()
                        ->where('status', 'completed')
                        ->whereNotNull('cv_waste_type')
                        ->select('cv_waste_type', DB::raw('count(*) as count'))
                        ->groupBy('cv_waste_type')
                        ->pluck('count', 'cv_waste_type'),
                ],
                'performance' => [
                    'avg_processing_time' => $rvm->deposits()
                        ->where('status', 'completed')
                        ->whereNotNull('processed_at')
                        ->selectRaw('AVG(EXTRACT(EPOCH FROM (processed_at - created_at))) as avg_seconds')
                        ->value('avg_seconds'),
                    'success_rate' => $rvm->deposits()->count() > 0 
                        ? round(($rvm->deposits()->where('status', 'completed')->count() / $rvm->deposits()->count()) * 100, 2)
                        : 0,
                ],
            ];

            return response()->json([
                'success' => true,
                'message' => 'RVM statistics retrieved successfully',
                'data' => $statistics,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve RVM statistics',
                'error' => $e->getMessage(),
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
    public function updateRVMStatus(Request $request, int $id): JsonResponse
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:active,inactive,maintenance,full',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $rvm->update([
                'status' => $request->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'RVM status updated successfully',
                'data' => [
                    'id' => $rvm->id,
                    'name' => $rvm->name,
                    'status' => $rvm->status,
                    'updated_at' => $rvm->updated_at,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update RVM status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Regenerate RVM API key
     *
     * @param int $id
     * @return JsonResponse
     */
    public function regenerateAPIKey(int $id): JsonResponse
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);

            $newApiKey = 'rvm_' . Str::random(32);
            $rvm->update([
                'api_key' => $newApiKey,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'RVM API key regenerated successfully',
                'data' => [
                    'id' => $rvm->id,
                    'name' => $rvm->name,
                    'api_key' => $newApiKey,
                    'updated_at' => $rvm->updated_at,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate RVM API key',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
