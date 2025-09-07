<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\VoucherRedemption;
use App\Models\UserBalance;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    /**
     * Get available vouchers
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAvailableVouchers(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->get('per_page', 15);

            $vouchers = Voucher::where('is_active', true)
                ->where('valid_from', '<=', now())
                ->where('valid_until', '>=', now())
                ->where(function ($q) {
                    $q->where('stock', '>', 0)
                      ->orWhereRaw('total_redeemed < stock');
                })
                ->orderBy('cost', 'asc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Available vouchers retrieved successfully',
                'data' => $vouchers->map(function ($voucher) use ($user) {
                    // Check if user has already redeemed this voucher
                    $userRedemption = VoucherRedemption::where('user_id', $user->id)
                        ->where('voucher_id', $voucher->id)
                        ->first();

                    return [
                        'id' => $voucher->id,
                        'tenant_id' => $voucher->tenant_id,
                        'title' => $voucher->title,
                        'description' => $voucher->description,
                        'cost' => $voucher->cost,
                        'stock' => $voucher->stock,
                        'total_redeemed' => $voucher->total_redeemed,
                        'remaining_stock' => max(0, $voucher->stock - $voucher->total_redeemed),
                        'valid_from' => $voucher->valid_from,
                        'valid_until' => $voucher->valid_until,
                        'is_redeemed' => $userRedemption ? true : false,
                        'redeemed_at' => $userRedemption ? $userRedemption->redeemed_at : null
                    ];
                }),
                'pagination' => [
                    'current_page' => $vouchers->currentPage(),
                    'per_page' => $vouchers->perPage(),
                    'total' => $vouchers->total(),
                    'last_page' => $vouchers->lastPage(),
                    'from' => $vouchers->firstItem(),
                    'to' => $vouchers->lastItem()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve available vouchers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Redeem a voucher
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function redeemVoucher(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $voucherId = $request->input('voucher_id');

            // Validate input
            if (!$voucherId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voucher ID is required'
                ], 400);
            }

            DB::beginTransaction();

            // Get voucher
            $voucher = Voucher::where('id', $voucherId)
                ->where('is_active', true)
                ->first();

            if (!$voucher) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Voucher not found or inactive'
                ], 404);
            }

            // Check if voucher is still valid
            if ($voucher->valid_from > now() || $voucher->valid_until < now()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Voucher is not valid at this time'
                ], 400);
            }

            // Check stock availability
            if ($voucher->stock <= 0 || $voucher->total_redeemed >= $voucher->stock) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Voucher is out of stock'
                ], 400);
            }

            // Check if user already redeemed this voucher
            $existingRedemption = VoucherRedemption::where('user_id', $user->id)
                ->where('voucher_id', $voucher->id)
                ->first();

            if ($existingRedemption) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'You have already redeemed this voucher'
                ], 400);
            }

            // Get user balance
            $userBalance = UserBalance::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'currency' => 'IDR']
            );

            // Check if user has enough balance to redeem voucher
            if ($userBalance->balance < $voucher->cost) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance to redeem voucher',
                    'data' => [
                        'required_balance' => $voucher->cost,
                        'current_balance' => $userBalance->balance
                    ]
                ], 400);
            }

            // Create voucher redemption record
            $redemption = VoucherRedemption::create([
                'user_id' => $user->id,
                'voucher_id' => $voucher->id,
                'redemption_code' => 'VRM' . strtoupper(uniqid()),
                'redeemed_at' => now(),
                'cost_at_redemption' => $voucher->cost
            ]);

            // Update voucher redemption count
            $voucher->increment('total_redeemed');

            // Deduct cost from user balance
            $userBalance->decrement('balance', $voucher->cost);

            // Create transaction record
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'user_balance_id' => $userBalance->id,
                'type' => 'debit',
                'amount' => $voucher->cost,
                'balance_before' => $userBalance->balance + $voucher->cost,
                'balance_after' => $userBalance->balance,
                'description' => "Voucher redemption: {$voucher->title}",
                'sourceable_type' => VoucherRedemption::class,
                'sourceable_id' => $redemption->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Voucher redeemed successfully',
                'data' => [
                    'redemption_id' => $redemption->id,
                    'redemption_code' => $redemption->redemption_code,
                    'voucher' => [
                        'id' => $voucher->id,
                        'title' => $voucher->title,
                        'description' => $voucher->description,
                        'cost' => $voucher->cost
                    ],
                    'new_balance' => $userBalance->fresh()->balance,
                    'transaction_id' => $transaction->id
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to redeem voucher',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
