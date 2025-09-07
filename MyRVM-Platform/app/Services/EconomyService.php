<?php

namespace App\Services;

use App\Models\UserBalance;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Models\VoucherRedemption;
use App\Models\Deposit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EconomyService
{
    /**
     * Add reward to user balance from deposit
     *
     * @param Deposit $deposit
     * @return array
     */
    public function addRewardToUserBalance(Deposit $deposit): array
    {
        try {
            DB::beginTransaction();

            // Get or create user balance
            $userBalance = UserBalance::firstOrCreate(
                ['user_id' => $deposit->user_id],
                ['balance' => 0, 'currency' => 'IDR']
            );

            // Add reward to balance
            $userBalance->increment('balance', $deposit->reward_amount);

            // Create transaction record
            $transaction = Transaction::create([
                'user_id' => $deposit->user_id,
                'user_balance_id' => $userBalance->id,
                'type' => 'credit',
                'amount' => $deposit->reward_amount,
                'balance_before' => $userBalance->balance - $deposit->reward_amount,
                'balance_after' => $userBalance->balance,
                'description' => "Reward for {$deposit->waste_type} deposit (ID: {$deposit->id})",
                'sourceable_type' => Deposit::class,
                'sourceable_id' => $deposit->id,
            ]);

            DB::commit();

            Log::info('Reward added to user balance', [
                'user_id' => $deposit->user_id,
                'deposit_id' => $deposit->id,
                'reward_amount' => $deposit->reward_amount,
                'new_balance' => $userBalance->fresh()->balance,
                'transaction_id' => $transaction->id
            ]);

            return [
                'success' => true,
                'user_balance' => $userBalance->fresh(),
                'transaction' => $transaction
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to add reward to user balance', [
                'user_id' => $deposit->user_id,
                'deposit_id' => $deposit->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Redeem voucher and deduct balance
     *
     * @param int $userId
     * @param int $voucherId
     * @return array
     */
    public function redeemVoucher(int $userId, int $voucherId): array
    {
        try {
            DB::beginTransaction();

            // Get voucher
            $voucher = Voucher::where('id', $voucherId)
                ->where('is_active', true)
                ->first();

            if (!$voucher) {
                DB::rollBack();
                return [
                    'success' => false,
                    'error' => 'Voucher not found or inactive'
                ];
            }

            // Check if voucher is still valid
            if ($voucher->valid_from > now() || $voucher->valid_until < now()) {
                DB::rollBack();
                return [
                    'success' => false,
                    'error' => 'Voucher is not valid at this time'
                ];
            }

            // Check stock availability
            if ($voucher->stock <= 0 || $voucher->total_redeemed >= $voucher->stock) {
                DB::rollBack();
                return [
                    'success' => false,
                    'error' => 'Voucher is out of stock'
                ];
            }

            // Check if user already redeemed this voucher
            $existingRedemption = VoucherRedemption::where('user_id', $userId)
                ->where('voucher_id', $voucher->id)
                ->first();

            if ($existingRedemption) {
                DB::rollBack();
                return [
                    'success' => false,
                    'error' => 'You have already redeemed this voucher'
                ];
            }

            // Get user balance
            $userBalance = UserBalance::firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0, 'currency' => 'IDR']
            );

            // Check if user has enough balance to redeem voucher
            if ($userBalance->balance < $voucher->cost) {
                DB::rollBack();
                return [
                    'success' => false,
                    'error' => 'Insufficient balance to redeem voucher',
                    'data' => [
                        'required_balance' => $voucher->cost,
                        'current_balance' => $userBalance->balance
                    ]
                ];
            }

            // Create voucher redemption record
            $redemption = VoucherRedemption::create([
                'user_id' => $userId,
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
                'user_id' => $userId,
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

            Log::info('Voucher redeemed successfully', [
                'user_id' => $userId,
                'voucher_id' => $voucher->id,
                'redemption_id' => $redemption->id,
                'redemption_code' => $redemption->redemption_code,
                'cost' => $voucher->cost,
                'new_balance' => $userBalance->fresh()->balance,
                'transaction_id' => $transaction->id
            ]);

            return [
                'success' => true,
                'redemption' => $redemption,
                'voucher' => $voucher,
                'user_balance' => $userBalance->fresh(),
                'transaction' => $transaction
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to redeem voucher', [
                'user_id' => $userId,
                'voucher_id' => $voucherId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get user's economy summary
     *
     * @param int $userId
     * @return array
     */
    public function getUserEconomySummary(int $userId): array
    {
        try {
            // Get user balance
            $userBalance = UserBalance::firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0, 'currency' => 'IDR']
            );

            // Get transaction statistics
            $transactionStats = Transaction::where('user_id', $userId)
                ->selectRaw('
                    COUNT(*) as total_transactions,
                    SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as total_credits,
                    SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as total_debits,
                    COUNT(CASE WHEN type = ? THEN 1 END) as credit_count,
                    COUNT(CASE WHEN type = ? THEN 1 END) as debit_count
                ', ['credit', 'debit', 'credit', 'debit'])
                ->first();

            // Get deposit statistics
            $depositStats = Deposit::where('user_id', $userId)
                ->selectRaw('
                    COUNT(*) as total_deposits,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_deposits,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_deposits,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as rejected_deposits,
                    SUM(reward_amount) as total_rewards,
                    AVG(ai_confidence) as avg_confidence
                ', ['completed', 'pending', 'rejected'])
                ->first();

            // Get voucher redemption statistics
            $voucherStats = VoucherRedemption::where('user_id', $userId)
                ->with('voucher')
                ->get()
                ->groupBy(function ($redemption) {
                    return $redemption->voucher->tenant_id;
                })
                ->map(function ($redemptions) {
                    return [
                        'count' => $redemptions->count(),
                        'total_cost' => $redemptions->sum('cost_at_redemption'),
                        'vouchers' => $redemptions->map(function ($redemption) {
                            return [
                                'id' => $redemption->id,
                                'voucher_title' => $redemption->voucher->title,
                                'redemption_code' => $redemption->redemption_code,
                                'cost' => $redemption->cost_at_redemption,
                                'redeemed_at' => $redemption->redeemed_at
                            ];
                        })
                    ];
                });

            return [
                'success' => true,
                'data' => [
                    'user_balance' => [
                        'current_balance' => $userBalance->balance,
                        'currency' => $userBalance->currency
                    ],
                    'transaction_summary' => [
                        'total_transactions' => $transactionStats->total_transactions ?? 0,
                        'total_credits' => $transactionStats->total_credits ?? 0,
                        'total_debits' => $transactionStats->total_debits ?? 0,
                        'credit_count' => $transactionStats->credit_count ?? 0,
                        'debit_count' => $transactionStats->debit_count ?? 0,
                        'net_balance' => ($transactionStats->total_credits ?? 0) - ($transactionStats->total_debits ?? 0)
                    ],
                    'deposit_summary' => [
                        'total_deposits' => $depositStats->total_deposits ?? 0,
                        'completed_deposits' => $depositStats->completed_deposits ?? 0,
                        'pending_deposits' => $depositStats->pending_deposits ?? 0,
                        'rejected_deposits' => $depositStats->rejected_deposits ?? 0,
                        'total_rewards' => $depositStats->total_rewards ?? 0,
                        'avg_confidence' => $depositStats->avg_confidence ?? 0
                    ],
                    'voucher_summary' => [
                        'total_redemptions' => $voucherStats->sum('count'),
                        'total_spent' => $voucherStats->sum('total_cost'),
                        'by_tenant' => $voucherStats->toArray()
                    ]
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get user economy summary', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Calculate reward amount based on waste type and quality
     *
     * @param string $wasteType
     * @param float $weight
     * @param string $qualityGrade
     * @param float $confidence
     * @return float
     */
    public function calculateRewardAmount(string $wasteType, float $weight, string $qualityGrade, float $confidence): float
    {
        // Base rate per kg by waste type
        $baseRates = [
            'plastic' => 5000.00,
            'glass' => 3000.00,
            'metal' => 8000.00,
            'paper' => 2000.00,
            'organic' => 1000.00,
        ];

        $baseRate = $baseRates[$wasteType] ?? 1000.00;

        // Quality multiplier
        $qualityMultipliers = [
            'A' => 1.2,
            'B' => 1.0,
            'C' => 0.8,
            'D' => 0.5,
        ];

        $qualityMultiplier = $qualityMultipliers[$qualityGrade] ?? 0.3;

        // Confidence factor
        $confidenceFactor = $confidence / 100;

        // Calculate reward
        $reward = $baseRate * $weight * $qualityMultiplier * $confidenceFactor;

        return round($reward, 2);
    }

    /**
     * Validate economy transaction
     *
     * @param string $type
     * @param float $amount
     * @param int $userId
     * @return array
     */
    public function validateEconomyTransaction(string $type, float $amount, int $userId): array
    {
        try {
            // Validate transaction type
            if (!in_array($type, ['credit', 'debit'])) {
                return [
                    'valid' => false,
                    'error' => 'Invalid transaction type'
                ];
            }

            // Validate amount
            if ($amount <= 0) {
                return [
                    'valid' => false,
                    'error' => 'Amount must be greater than 0'
                ];
            }

            // For debit transactions, check if user has enough balance
            if ($type === 'debit') {
                $userBalance = UserBalance::firstOrCreate(
                    ['user_id' => $userId],
                    ['balance' => 0, 'currency' => 'IDR']
                );

                if ($userBalance->balance < $amount) {
                    return [
                        'valid' => false,
                        'error' => 'Insufficient balance',
                        'data' => [
                            'required_amount' => $amount,
                            'current_balance' => $userBalance->balance
                        ]
                    ];
                }
            }

            return [
                'valid' => true,
                'message' => 'Transaction is valid'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to validate economy transaction', [
                'type' => $type,
                'amount' => $amount,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [
                'valid' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get economy analytics for admin
     *
     * @param array $filters
     * @return array
     */
    public function getEconomyAnalytics(array $filters = []): array
    {
        try {
            $startDate = $filters['start_date'] ?? now()->subDays(30);
            $endDate = $filters['end_date'] ?? now();

            // Total transactions
            $totalTransactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
                ->count();

            // Total credits and debits
            $transactionTotals = Transaction::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('
                    SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as total_credits,
                    SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as total_debits
                ', ['credit', 'debit'])
                ->first();

            // Total deposits
            $totalDeposits = Deposit::whereBetween('created_at', [$startDate, $endDate])
                ->count();

            // Total rewards given
            $totalRewards = Deposit::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')
                ->sum('reward_amount');

            // Total voucher redemptions
            $totalVoucherRedemptions = VoucherRedemption::whereBetween('redeemed_at', [$startDate, $endDate])
                ->count();

            // Total voucher spending
            $totalVoucherSpending = VoucherRedemption::whereBetween('redeemed_at', [$startDate, $endDate])
                ->sum('cost_at_redemption');

            // Active users
            $activeUsers = UserBalance::where('balance', '>', 0)
                ->count();

            return [
                'success' => true,
                'data' => [
                    'period' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ],
                    'transactions' => [
                        'total_count' => $totalTransactions,
                        'total_credits' => $transactionTotals->total_credits ?? 0,
                        'total_debits' => $transactionTotals->total_debits ?? 0,
                        'net_flow' => ($transactionTotals->total_credits ?? 0) - ($transactionTotals->total_debits ?? 0)
                    ],
                    'deposits' => [
                        'total_count' => $totalDeposits,
                        'total_rewards' => $totalRewards
                    ],
                    'vouchers' => [
                        'total_redemptions' => $totalVoucherRedemptions,
                        'total_spending' => $totalVoucherSpending
                    ],
                    'users' => [
                        'active_users' => $activeUsers
                    ]
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get economy analytics', [
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
