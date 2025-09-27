<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\VoucherRedemption;
use App\Models\UserBalance;
use App\Models\Transaction;
use App\Services\EconomyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    protected EconomyService $economyService;

    public function __construct(EconomyService $economyService)
    {
        $this->economyService = $economyService;
    }
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

            // Use EconomyService to redeem voucher
            $result = $this->economyService->redeemVoucher($user->id, $voucherId);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'],
                    'data' => $result['data'] ?? null
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Voucher redeemed successfully',
                'data' => [
                    'redemption_id' => $result['redemption']->id,
                    'redemption_code' => $result['redemption']->redemption_code,
                    'voucher' => [
                        'id' => $result['voucher']->id,
                        'title' => $result['voucher']->title,
                        'description' => $result['voucher']->description,
                        'cost' => $result['voucher']->cost
                    ],
                    'new_balance' => $result['user_balance']->balance,
                    'transaction_id' => $result['transaction']->id
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to redeem voucher',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
