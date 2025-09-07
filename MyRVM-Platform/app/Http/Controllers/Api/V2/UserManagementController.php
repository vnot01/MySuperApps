<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\UserBalance;
use App\Models\Deposit;
use App\Models\VoucherRedemption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        // TODO: Add role middleware when available
        // $this->middleware('role:admin|superadmin');
    }

    /**
     * Get all users with pagination and filtering
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
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone_number', 'like', "%{$search}%");
                });
            }

            if ($request->has('role_id')) {
                $roleId = $request->input('role_id');
                $query->whereHas('role', function ($q) use ($roleId) {
                    $q->where('id', $roleId);
                });
            }

            if ($request->has('status')) {
                $status = $request->input('status');
                if ($status === 'active') {
                    $query->where('email_verified_at', '!=', null);
                } elseif ($status === 'inactive') {
                    $query->where('email_verified_at', null);
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
                    'phone_number' => $user->phone_number,
                    'email_verified_at' => $user->email_verified_at,
                    'role' => $user->role ? $user->role->name : null,
                    'balance' => $user->balance ? $user->balance->balance : 0,
                    'deposits_count' => $user->deposits()->count(),
                    'voucher_redemptions_count' => $user->voucherRedemptions()->count(),
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
     * Get user details by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getUser(int $id): JsonResponse
    {
        try {
            $user = User::with(['balance', 'role', 'deposits', 'voucherRedemptions'])->findOrFail($id);

            $data = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'email_verified_at' => $user->email_verified_at,
                'role' => $user->role ? [
                    'id' => $user->role->id,
                    'name' => $user->role->name,
                ] : null,
                'balance' => $user->balance ? [
                    'balance' => $user->balance->balance,
                    'updated_at' => $user->balance->updated_at,
                ] : null,
                'statistics' => [
                    'deposits_count' => $user->deposits()->count(),
                    'completed_deposits' => $user->deposits()->where('status', 'completed')->count(),
                    'total_rewards_earned' => $user->deposits()->where('status', 'completed')->sum('reward_amount'),
                    'voucher_redemptions_count' => $user->voucherRedemptions()->count(),
                    'total_vouchers_redeemed' => $user->voucherRedemptions()->sum('cost_at_redemption'),
                ],
                'recent_deposits' => $user->deposits()
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($deposit) {
                        return [
                            'id' => $deposit->id,
                            'rvm_id' => $deposit->rvm_id,
                            'status' => $deposit->status,
                            'reward_amount' => $deposit->reward_amount,
                            'cv_confidence' => $deposit->cv_confidence,
                            'cv_waste_type' => $deposit->cv_waste_type,
                            'created_at' => $deposit->created_at,
                        ];
                    }),
                'recent_voucher_redemptions' => $user->voucherRedemptions()
                    ->with('voucher')
                    ->orderBy('redeemed_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($redemption) {
                        return [
                            'id' => $redemption->id,
                            'voucher_title' => $redemption->voucher ? $redemption->voucher->title : 'N/A',
                            'redemption_code' => $redemption->redemption_code,
                            'cost_at_redemption' => $redemption->cost_at_redemption,
                            'redeemed_at' => $redemption->redeemed_at,
                            'used_at' => $redemption->used_at,
                        ];
                    }),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];

            return response()->json([
                'success' => true,
                'message' => 'User details retrieved successfully',
                'data' => $data,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user details',
                'error' => $e->getMessage(),
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
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'phone_number' => 'nullable|string|max:20',
                'role_id' => 'required|exists:roles,id',
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
                'phone_number' => $request->phone_number,
                'role_id' => $request->role_id,
                'email_verified_at' => now(), // Auto-verify for admin-created users
            ]);

            // Create user balance
            UserBalance::create([
                'user_id' => $user->id,
                'balance' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'role_id' => $user->role_id,
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
     * Update an existing user
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
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
                'password' => 'sometimes|required|string|min:8|confirmed',
                'phone_number' => 'nullable|string|max:20',
                'role_id' => 'sometimes|required|exists:roles,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $updateData = $request->only(['name', 'email', 'phone_number', 'role_id']);
            
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
                    'phone_number' => $user->phone_number,
                    'role_id' => $user->role_id,
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

            // Check if user has associated data
            $depositsCount = $user->deposits()->count();
            $voucherRedemptionsCount = $user->voucherRedemptions()->count();

            if ($depositsCount > 0 || $voucherRedemptionsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete user with associated data',
                    'data' => [
                        'deposits_count' => $depositsCount,
                        'voucher_redemptions_count' => $voucherRedemptionsCount,
                    ],
                ], 409);
            }

            // Delete user balance first
            $user->balance()->delete();
            
            // Delete user
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
     * Get user statistics
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getUserStatistics(int $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $statistics = [
                'user_info' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ? $user->role->name : null,
                ],
                'balance' => [
                    'current_balance' => $user->balance ? $user->balance->balance : 0,
                    'total_earned' => $user->deposits()->where('status', 'completed')->sum('reward_amount'),
                    'total_spent' => $user->voucherRedemptions()->sum('cost_at_redemption'),
                ],
                'deposits' => [
                    'total' => $user->deposits()->count(),
                    'completed' => $user->deposits()->where('status', 'completed')->count(),
                    'pending' => $user->deposits()->where('status', 'pending')->count(),
                    'processing' => $user->deposits()->where('status', 'processing')->count(),
                    'rejected' => $user->deposits()->where('status', 'rejected')->count(),
                    'avg_reward' => $user->deposits()->where('status', 'completed')->avg('reward_amount'),
                ],
                'voucher_redemptions' => [
                    'total' => $user->voucherRedemptions()->count(),
                    'used' => $user->voucherRedemptions()->whereNotNull('used_at')->count(),
                    'unused' => $user->voucherRedemptions()->whereNull('used_at')->count(),
                    'total_cost' => $user->voucherRedemptions()->sum('cost_at_redemption'),
                ],
                'activity' => [
                    'first_deposit' => $user->deposits()->orderBy('created_at', 'asc')->first()?->created_at,
                    'last_deposit' => $user->deposits()->orderBy('created_at', 'desc')->first()?->created_at,
                    'first_redemption' => $user->voucherRedemptions()->orderBy('redeemed_at', 'asc')->first()?->redeemed_at,
                    'last_redemption' => $user->voucherRedemptions()->orderBy('redeemed_at', 'desc')->first()?->redeemed_at,
                ],
            ];

            return response()->json([
                'success' => true,
                'message' => 'User statistics retrieved successfully',
                'data' => $statistics,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all available roles
     *
     * @return JsonResponse
     */
    public function getRoles(): JsonResponse
    {
        try {
            $roles = Role::select('id', 'name', 'slug')->get();

            return response()->json([
                'success' => true,
                'message' => 'Roles retrieved successfully',
                'data' => $roles,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve roles',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user balance (admin only)
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateUserBalance(Request $request, int $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'balance' => 'required|numeric|min:0',
                'reason' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $oldBalance = $user->balance ? $user->balance->balance : 0;
            $newBalance = $request->balance;
            $difference = $newBalance - $oldBalance;

            // Update or create user balance
            $userBalance = UserBalance::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0]
            );

            $userBalance->update(['balance' => $newBalance]);

            // Create transaction record
            $user->transactions()->create([
                'user_balance_id' => $userBalance->id,
                'type' => $difference >= 0 ? 'credit' : 'debit',
                'amount' => abs($difference),
                'balance_before' => $oldBalance,
                'balance_after' => $newBalance,
                'description' => $request->reason,
                'sourceable_type' => 'App\\Models\\User',
                'sourceable_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User balance updated successfully',
                'data' => [
                    'user_id' => $user->id,
                    'old_balance' => $oldBalance,
                    'new_balance' => $newBalance,
                    'difference' => $difference,
                    'reason' => $request->reason,
                    'updated_at' => $userBalance->updated_at,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user balance',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
