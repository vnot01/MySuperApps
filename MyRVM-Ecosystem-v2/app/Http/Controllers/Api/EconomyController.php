<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserBalance;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Models\VoucherRedemption;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class EconomyController extends Controller
{
    public function getBalance(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->balance) {
            $user->balance()->create([
                'balance' => 0,
                'currency' => 'IDR'
            ]);
            $user->load('balance');
        }

        return response()->json([
            'success' => true,
            'balance' => $user->balance->balance,
            'formatted_balance' => $user->balance->formatted_balance,
            'currency' => $user->balance->currency
        ]);
    }

    public function getTransactions(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $transactions = $user->transactions()
            ->with('sourceable')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'transactions' => $transactions
        ]);
    }

    public function addBalance(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $transaction = $request->user()->addBalance(
                $request->amount,
                $request->description ?? 'Balance added'
            );

            return response()->json([
                'success' => true,
                'transaction' => $transaction,
                'new_balance' => $request->user()->getBalance()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deductBalance(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $transaction = $request->user()->deductBalance(
                $request->amount,
                $request->description ?? 'Balance deducted'
            );

            return response()->json([
                'success' => true,
                'transaction' => $transaction,
                'new_balance' => $request->user()->getBalance()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getAvailableVouchers(Request $request): JsonResponse
    {
        $vouchers = Voucher::available()
            ->select(['id', 'code', 'name', 'description', 'discount_type', 'discount_value', 'min_purchase', 'max_discount', 'valid_until'])
            ->get();

        return response()->json([
            'success' => true,
            'vouchers' => $vouchers
        ]);
    }

    public function redeemVoucher(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'voucher_code' => 'required|string|max:50',
            'purchase_amount' => 'required|numeric|min:0.01'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $voucher = Voucher::where('code', $request->voucher_code)->first();

            if (!$voucher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voucher not found'
                ], 404);
            }

            $redemption = $voucher->redeem($request->user()->id, $request->purchase_amount);

            return response()->json([
                'success' => true,
                'redemption' => $redemption,
                'discount_amount' => $redemption->discount_amount,
                'formatted_discount' => $redemption->formatted_discount_amount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getVoucherHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $redemptions = $user->voucherRedemptions()
            ->with('voucher:id,code,name,discount_type')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'redemptions' => $redemptions
        ]);
    }

    public function calculateReward(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'waste_type' => 'required|string|in:plastic,glass,metal,paper,other',
            'weight' => 'required|numeric|min:0.001',
            'quality_grade' => 'required|string|in:A,B,C,D',
            'confidence' => 'nullable|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $reward = $this->calculateRewardAmount(
            $request->waste_type,
            $request->weight,
            $request->quality_grade,
            $request->confidence ?? 50
        );

        return response()->json([
            'success' => true,
            'reward_amount' => $reward,
            'formatted_reward' => number_format($reward, 2) . ' IDR',
            'breakdown' => [
                'waste_type' => $request->waste_type,
                'weight' => $request->weight,
                'quality_grade' => $request->quality_grade,
                'confidence' => $request->confidence ?? 50
            ]
        ]);
    }

    private function calculateRewardAmount(string $wasteType, float $weight, string $qualityGrade, float $confidence): float
    {
        // Base rates per kg
        $baseRates = [
            'plastic' => 5000.00,
            'glass' => 3000.00,
            'metal' => 8000.00,
            'paper' => 2000.00,
            'other' => 1000.00
        ];

        // Quality multipliers
        $qualityMultipliers = [
            'A' => 1.2,
            'B' => 1.0,
            'C' => 0.8,
            'D' => 0.5
        ];

        $baseRate = $baseRates[$wasteType] ?? 1000.00;
        $qualityMultiplier = $qualityMultipliers[$qualityGrade] ?? 1.0;
        $confidenceFactor = $confidence / 100;

        return $baseRate * $weight * $qualityMultiplier * $confidenceFactor;
    }
}
