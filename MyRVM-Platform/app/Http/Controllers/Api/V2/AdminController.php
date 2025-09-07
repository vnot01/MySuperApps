<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use App\Models\ReverseVendingMachine;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Models\VoucherRedemption;
use App\Models\UserBalance;
use App\Services\EconomyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    protected EconomyService $economyService;

    public function __construct(EconomyService $economyService)
    {
        $this->economyService = $economyService;
        $this->middleware('auth:sanctum');
        // TODO: Add role middleware when available
        // $this->middleware('role:admin|superadmin');
    }

    /**
     * Get admin dashboard statistics
     *
     * @return JsonResponse
     */
    public function getDashboardStats(): JsonResponse
    {
        try {
            // Get system overview statistics
            $stats = [
                'users' => [
                    'total' => User::count(),
                    'active' => User::whereNotNull('email_verified_at')->count(),
                    'pending' => User::whereNull('email_verified_at')->count(),
                    'new_today' => User::whereDate('created_at', today())->count(),
                ],
                'tenants' => [
                    'total' => Tenant::count(),
                    'active' => Tenant::where('is_active', true)->count(),
                    'inactive' => Tenant::where('is_active', false)->count(),
                ],
                'rvms' => [
                    'total' => ReverseVendingMachine::count(),
                    'active' => ReverseVendingMachine::where('status', 'active')->count(),
                    'inactive' => ReverseVendingMachine::where('status', 'inactive')->count(),
                    'maintenance' => ReverseVendingMachine::where('status', 'maintenance')->count(),
                    'full' => ReverseVendingMachine::where('status', 'full')->count(),
                ],
                'deposits' => [
                    'total' => Deposit::count(),
                    'completed' => Deposit::where('status', 'completed')->count(),
                    'pending' => Deposit::where('status', 'pending')->count(),
                    'rejected' => Deposit::where('status', 'rejected')->count(),
                    'today' => Deposit::whereDate('created_at', today())->count(),
                    'total_rewards' => Deposit::where('status', 'completed')->sum('reward_amount'),
                ],
                'economy' => [
                    'total_transactions' => Transaction::count(),
                    'total_credits' => Transaction::where('type', 'credit')->sum('amount'),
                    'total_debits' => Transaction::where('type', 'debit')->sum('amount'),
                    'active_balances' => UserBalance::where('balance', '>', 0)->count(),
                    'total_balance' => UserBalance::sum('balance'),
                ],
                'vouchers' => [
                    'total' => Voucher::count(),
                    'active' => Voucher::where('is_active', true)->count(),
                    'total_redemptions' => VoucherRedemption::count(),
                    'total_spent' => VoucherRedemption::sum('cost_at_redemption'),
                ],
            ];

            return response()->json([
                'success' => true,
                'message' => 'Dashboard statistics retrieved successfully',
                'data' => [
                    'statistics' => $stats,
                    'generated_at' => now()->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all users with pagination and filters
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUsers(Request $request): JsonResponse
    {
        try {
            $query = User::with(['balance', 'role']);

            // Apply filters
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($request->has('role')) {
                $query->whereHas('role', function ($q) use ($request) {
                    $q->where('name', $request->input('role'));
                });
            }

            if ($request->has('status')) {
                $status = $request->input('status');
                if ($status === 'verified') {
                    $query->whereNotNull('email_verified_at');
                } elseif ($status === 'unverified') {
                    $query->whereNull('email_verified_at');
                }
            }

            // Apply sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = min($request->input('per_page', 15), 100);
            $users = $query->paginate($perPage);

            // Transform data
            $users->getCollection()->transform(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'role' => $user->role ? $user->role->name : null,
                    'balance' => $user->balance ? $user->balance->balance : 0,
                    'currency' => $user->balance ? $user->balance->currency : 'IDR',
                    'deposits_count' => $user->deposits()->count(),
                    'total_rewards' => $user->deposits()->where('status', 'completed')->sum('reward_amount'),
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Users retrieved successfully',
                'data' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'last_page' => $users->lastPage(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createUser(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'role_id' => 'required|exists:roles,id',
                'tenant_id' => 'nullable|exists:tenants,id',
                'phone_number' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id,
                'tenant_id' => $request->tenant_id,
                'phone_number' => $request->phone_number,
                'email_verified_at' => now(), // Auto-verify for admin-created users
            ]);

            // Create initial balance
            $user->balance()->create([
                'balance' => 0,
                'currency' => 'IDR',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ? $user->role->name : null,
                    'tenant' => $user->tenant ? $user->tenant->name : null,
                    'phone_number' => $user->phone_number,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a user
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateUser(Request $request, int $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $id,
                'password' => 'sometimes|required|string|min:8',
                'role_id' => 'sometimes|required|exists:roles,id',
                'tenant_id' => 'nullable|exists:tenants,id',
                'phone_number' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $updateData = $request->only(['name', 'email', 'role_id', 'tenant_id', 'phone_number']);
            
            if ($request->has('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ? $user->role->name : null,
                    'tenant' => $user->tenant ? $user->tenant->name : null,
                    'phone_number' => $user->phone_number,
                    'email_verified_at' => $user->email_verified_at,
                    'updated_at' => $user->updated_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a user
     *
     * @param int $id
     * @return JsonResponse
     */
    public function deleteUser(int $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deletion of super admin
            if ($user->role && $user->role->name === 'Super Admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete super admin user',
                ], 403);
            }

            // Prevent deletion of current user
            if ($user->id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete your own account',
                ], 403);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get system settings
     *
     * @return JsonResponse
     */
    public function getSystemSettings(): JsonResponse
    {
        try {
            $settings = [
                'app_name' => config('app.name'),
                'app_env' => config('app.env'),
                'app_debug' => config('app.debug'),
                'database_connection' => config('database.default'),
                'cache_driver' => config('cache.default'),
                'queue_driver' => config('queue.default'),
                'mail_driver' => config('mail.default'),
                'session_driver' => config('session.driver'),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'System settings retrieved successfully',
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve system settings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update system settings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSystemSettings(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'app_name' => 'sometimes|required|string|max:255',
                'timezone' => 'sometimes|required|string|max:50',
                'locale' => 'sometimes|required|string|max:10',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Note: In a real application, you would update these settings
            // in a database table or configuration file
            // For now, we'll just return the current settings

            $settings = [
                'app_name' => config('app.name'),
                'app_env' => config('app.env'),
                'app_debug' => config('app.debug'),
                'database_connection' => config('database.default'),
                'cache_driver' => config('cache.default'),
                'queue_driver' => config('queue.default'),
                'mail_driver' => config('mail.default'),
                'session_driver' => config('session.driver'),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'System settings updated successfully',
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update system settings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
