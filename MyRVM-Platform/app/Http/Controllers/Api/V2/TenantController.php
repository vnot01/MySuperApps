<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Voucher;
use App\Models\ReverseVendingMachine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TenantController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        // TODO: Add role middleware when available
        // $this->middleware('role:admin|superadmin|tenant');
    }

    /**
     * Get all tenants with pagination and filtering
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTenants(Request $request): JsonResponse
    {
        try {
            $query = Tenant::query();

            // Apply filters
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->has('status')) {
                $status = $request->input('status');
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            // Apply sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = min($request->input('per_page', 15), 100);
            $tenants = $query->paginate($perPage);

            // Transform data
            $tenants->getCollection()->transform(function ($tenant) {
                return [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'description' => $tenant->description,
                    'is_active' => $tenant->is_active,
                    'users_count' => $tenant->users()->count(),
                    'vouchers_count' => $tenant->vouchers()->count(),
                    'rvms_count' => 0, // RVM table doesn't have tenant_id column yet
                    'created_at' => $tenant->created_at,
                    'updated_at' => $tenant->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Tenants retrieved successfully',
                'data' => $tenants->items(),
                'pagination' => [
                    'current_page' => $tenants->currentPage(),
                    'per_page' => $tenants->perPage(),
                    'total' => $tenants->total(),
                    'last_page' => $tenants->lastPage(),
                    'from' => $tenants->firstItem(),
                    'to' => $tenants->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tenants',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tenant details by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getTenant(int $id): JsonResponse
    {
        try {
            $tenant = Tenant::with(['users', 'vouchers'])->findOrFail($id);

            $data = [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'description' => $tenant->description,
                'is_active' => $tenant->is_active,
                'statistics' => [
                    'users_count' => $tenant->users()->count(),
                    'vouchers_count' => $tenant->vouchers()->count(),
                    'rvms_count' => 0, // RVM table doesn't have tenant_id column yet
                    'active_vouchers' => $tenant->vouchers()->where('is_active', true)->count(),
                    'active_rvms' => 0, // RVM table doesn't have tenant_id column yet
                ],
                'users' => $tenant->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role ? $user->role->name : null,
                        'created_at' => $user->created_at,
                    ];
                }),
                'vouchers' => $tenant->vouchers->map(function ($voucher) {
                    return [
                        'id' => $voucher->id,
                        'title' => $voucher->title,
                        'description' => $voucher->description,
                        'cost' => $voucher->cost,
                        'stock' => $voucher->stock,
                        'total_redeemed' => $voucher->total_redeemed,
                        'is_active' => $voucher->is_active,
                        'valid_from' => $voucher->valid_from,
                        'valid_until' => $voucher->valid_until,
                    ];
                }),
                'reverse_vending_machines' => [], // RVM table doesn't have tenant_id column yet
                'created_at' => $tenant->created_at,
                'updated_at' => $tenant->updated_at,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Tenant details retrieved successfully',
                'data' => $data,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tenant details',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new tenant
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createTenant(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:tenants,name',
                'description' => 'nullable|string|max:1000',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $tenant = Tenant::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->input('is_active', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tenant created successfully',
                'data' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'description' => $tenant->description,
                    'is_active' => $tenant->is_active,
                    'created_at' => $tenant->created_at,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tenant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a tenant
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateTenant(Request $request, int $id): JsonResponse
    {
        try {
            $tenant = Tenant::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255|unique:tenants,name,' . $id,
                'description' => 'nullable|string|max:1000',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $updateData = $request->only(['name', 'description', 'is_active']);
            $tenant->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Tenant updated successfully',
                'data' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'description' => $tenant->description,
                    'is_active' => $tenant->is_active,
                    'updated_at' => $tenant->updated_at,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tenant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a tenant
     *
     * @param int $id
     * @return JsonResponse
     */
    public function deleteTenant(int $id): JsonResponse
    {
        try {
            $tenant = Tenant::findOrFail($id);

            // Check if tenant has associated data
            $usersCount = $tenant->users()->count();
            $vouchersCount = $tenant->vouchers()->count();
            $rvmsCount = 0; // RVM table doesn't have tenant_id column yet

            if ($usersCount > 0 || $vouchersCount > 0 || $rvmsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete tenant with associated data',
                    'data' => [
                        'users_count' => $usersCount,
                        'vouchers_count' => $vouchersCount,
                        'rvms_count' => $rvmsCount,
                    ],
                ], 409);
            }

            $tenant->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tenant deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tenant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get tenant statistics
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getTenantStatistics(int $id): JsonResponse
    {
        try {
            $tenant = Tenant::findOrFail($id);

            $statistics = [
                'tenant_info' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'is_active' => $tenant->is_active,
                ],
                'users' => [
                    'total' => $tenant->users()->count(),
                    'by_role' => $tenant->users()
                        ->join('roles', 'users.role_id', '=', 'roles.id')
                        ->select('roles.name', DB::raw('count(*) as count'))
                        ->groupBy('roles.name')
                        ->pluck('count', 'name'),
                ],
                'vouchers' => [
                    'total' => $tenant->vouchers()->count(),
                    'active' => $tenant->vouchers()->where('is_active', true)->count(),
                    'inactive' => $tenant->vouchers()->where('is_active', false)->count(),
                    'total_redeemed' => $tenant->vouchers()->sum('total_redeemed'),
                    'total_stock' => $tenant->vouchers()->sum('stock'),
                ],
                'reverse_vending_machines' => [
                    'total' => 0,
                    'active' => 0,
                    'inactive' => 0,
                    'maintenance' => 0,
                    'full' => 0,
                ],
                'deposits' => [
                    'total' => 0,
                    'completed' => 0,
                    'pending' => 0,
                ],
            ];

            return response()->json([
                'success' => true,
                'message' => 'Tenant statistics retrieved successfully',
                'data' => $statistics,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tenant statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle tenant status (active/inactive)
     *
     * @param int $id
     * @return JsonResponse
     */
    public function toggleTenantStatus(int $id): JsonResponse
    {
        try {
            $tenant = Tenant::findOrFail($id);

            $tenant->update([
                'is_active' => !$tenant->is_active,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tenant status updated successfully',
                'data' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'is_active' => $tenant->is_active,
                    'updated_at' => $tenant->updated_at,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tenant status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
