<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Deposit;
use App\Models\VoucherRedemption;
use App\Models\ReverseVendingMachine;
use App\Models\Tenant;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        // TODO: Add role middleware when available
        // $this->middleware('role:admin|superadmin');
    }

    /**
     * Get comprehensive dashboard analytics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getDashboardAnalytics(Request $request): JsonResponse
    {
        try {
            $period = $request->input('period', '30d'); // 7d, 30d, 90d, 1y
            $startDate = $this->getStartDate($period);
            $endDate = now();

            $analytics = [
                'period' => $period,
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'overview' => $this->getOverviewStats($startDate, $endDate),
                'users' => $this->getUserAnalyticsData($startDate, $endDate),
                'deposits' => $this->getDepositAnalyticsData($startDate, $endDate),
                'economy' => $this->getEconomyAnalyticsData($startDate, $endDate),
                'rvms' => $this->getRVMAnalyticsData($startDate, $endDate),
                'trends' => $this->getTrendAnalytics($startDate, $endDate),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Dashboard analytics retrieved successfully',
                'data' => $analytics,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard analytics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get deposit analytics with detailed breakdown
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getDepositAnalytics(Request $request): JsonResponse
    {
        try {
            $period = $request->input('period', '30d');
            $startDate = $this->getStartDate($period);
            $endDate = now();

            $analytics = [
                'period' => $period,
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'summary' => $this->getDepositAnalyticsData($startDate, $endDate),
                'by_status' => $this->getDepositsByStatus($startDate, $endDate),
                'by_waste_type' => $this->getDepositsByWasteType($startDate, $endDate),
                'by_rvm' => $this->getDepositsByRVM($startDate, $endDate),
                'daily_trends' => $this->getDailyDepositTrends($startDate, $endDate),
                'top_users' => $this->getTopDepositUsers($startDate, $endDate),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Deposit analytics retrieved successfully',
                'data' => $analytics,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve deposit analytics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get economy analytics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getEconomyAnalytics(Request $request): JsonResponse
    {
        try {
            $period = $request->input('period', '30d');
            $startDate = $this->getStartDate($period);
            $endDate = now();

            $analytics = [
                'period' => $period,
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'summary' => $this->getEconomyAnalyticsData($startDate, $endDate),
                'transactions' => $this->getTransactionAnalytics($startDate, $endDate),
                'voucher_redemptions' => $this->getVoucherRedemptionAnalytics($startDate, $endDate),
                'balance_distribution' => $this->getBalanceDistribution(),
                'revenue_trends' => $this->getRevenueTrends($startDate, $endDate),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Economy analytics retrieved successfully',
                'data' => $analytics,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve economy analytics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user analytics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserAnalytics(Request $request): JsonResponse
    {
        try {
            $period = $request->input('period', '30d');
            $startDate = $this->getStartDate($period);
            $endDate = now();

            $analytics = [
                'period' => $period,
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'summary' => $this->getUserAnalyticsData($startDate, $endDate),
                'registration_trends' => $this->getUserRegistrationTrends($startDate, $endDate),
                'activity_levels' => $this->getUserActivityLevels($startDate, $endDate),
                'by_role' => $this->getUsersByRole(),
                'top_contributors' => $this->getTopContributingUsers($startDate, $endDate),
            ];

            return response()->json([
                'success' => true,
                'message' => 'User analytics retrieved successfully',
                'data' => $analytics,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user analytics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get RVM performance analytics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getRVMAnalytics(Request $request): JsonResponse
    {
        try {
            $period = $request->input('period', '30d');
            $startDate = $this->getStartDate($period);
            $endDate = now();

            $analytics = [
                'period' => $period,
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'summary' => $this->getRVMAnalyticsData($startDate, $endDate),
                'performance_ranking' => $this->getRVMPerformanceRanking($startDate, $endDate),
                'utilization_rates' => $this->getRVMUtilizationRates($startDate, $endDate),
                'maintenance_insights' => $this->getMaintenanceInsights($startDate, $endDate),
            ];

            return response()->json([
                'success' => true,
                'message' => 'RVM analytics retrieved successfully',
                'data' => $analytics,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve RVM analytics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate custom report
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateReport(Request $request): JsonResponse
    {
        try {
            $validator = \Validator::make($request->all(), [
                'report_type' => 'required|in:deposits,economy,users,rvms,comprehensive',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'format' => 'in:json,csv,pdf',
                'filters' => 'array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $reportType = $request->report_type;
            $format = $request->input('format', 'json');
            $filters = $request->input('filters', []);

            $report = $this->generateCustomReport($reportType, $startDate, $endDate, $filters);

            return response()->json([
                'success' => true,
                'message' => 'Report generated successfully',
                'data' => [
                    'report_type' => $reportType,
                    'date_range' => [
                        'start' => $startDate->toDateString(),
                        'end' => $endDate->toDateString(),
                    ],
                    'format' => $format,
                    'generated_at' => now()->toISOString(),
                    'report' => $report,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Helper Methods

    /**
     * Get start date based on period
     */
    private function getStartDate(string $period): Carbon
    {
        return match ($period) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            '1y' => now()->subYear(),
            default => now()->subDays(30),
        };
    }

    /**
     * Get overview statistics
     */
    private function getOverviewStats(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('email_verified_at', '!=', null)->count(),
            'total_deposits' => Deposit::count(),
            'completed_deposits' => Deposit::where('status', 'completed')->count(),
            'total_rewards_given' => Deposit::where('status', 'completed')->sum('reward_amount'),
            'total_rvms' => ReverseVendingMachine::count(),
            'active_rvms' => ReverseVendingMachine::where('status', 'active')->count(),
            'total_voucher_redemptions' => VoucherRedemption::count(),
            'total_balance' => DB::table('user_balances')->sum('balance'),
        ];
    }

    /**
     * Get user analytics data
     */
    private function getUserAnalyticsData(Carbon $startDate, Carbon $endDate): array
    {
        $newUsers = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $activeUsers = User::whereHas('deposits', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        })->count();

        return [
            'new_users' => $newUsers,
            'active_users' => $activeUsers,
            'total_users' => User::count(),
            'user_growth_rate' => $this->calculateGrowthRate(
                User::where('created_at', '<', $startDate)->count(),
                $newUsers
            ),
        ];
    }

    /**
     * Get deposit analytics data
     */
    private function getDepositAnalyticsData(Carbon $startDate, Carbon $endDate): array
    {
        $totalDeposits = Deposit::whereBetween('created_at', [$startDate, $endDate])->count();
        $completedDeposits = Deposit::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')->count();
        $totalRewards = Deposit::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')->sum('reward_amount');

        return [
            'total_deposits' => $totalDeposits,
            'completed_deposits' => $completedDeposits,
            'pending_deposits' => Deposit::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'pending')->count(),
            'processing_deposits' => Deposit::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'processing')->count(),
            'rejected_deposits' => Deposit::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'rejected')->count(),
            'total_rewards_given' => $totalRewards,
            'avg_reward_per_deposit' => $completedDeposits > 0 ? $totalRewards / $completedDeposits : 0,
            'completion_rate' => $totalDeposits > 0 ? ($completedDeposits / $totalDeposits) * 100 : 0,
        ];
    }

    /**
     * Get economy analytics data
     */
    private function getEconomyAnalyticsData(Carbon $startDate, Carbon $endDate): array
    {
        $totalTransactions = Transaction::whereBetween('created_at', [$startDate, $endDate])->count();
        $totalCredits = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('type', 'credit')->sum('amount');
        $totalDebits = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('type', 'debit')->sum('amount');
        $voucherRedemptions = VoucherRedemption::whereBetween('redeemed_at', [$startDate, $endDate])->count();

        return [
            'total_transactions' => $totalTransactions,
            'total_credits' => $totalCredits,
            'total_debits' => $totalDebits,
            'net_flow' => $totalCredits - $totalDebits,
            'voucher_redemptions' => $voucherRedemptions,
            'avg_transaction_amount' => $totalTransactions > 0 ? ($totalCredits + $totalDebits) / $totalTransactions : 0,
        ];
    }

    /**
     * Get RVM analytics data
     */
    private function getRVMAnalyticsData(Carbon $startDate, Carbon $endDate): array
    {
        $totalRVMs = ReverseVendingMachine::count();
        $activeRVMs = ReverseVendingMachine::where('status', 'active')->count();
        $rvmsWithDeposits = ReverseVendingMachine::whereHas('deposits', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        })->count();

        return [
            'total_rvms' => $totalRVMs,
            'active_rvms' => $activeRVMs,
            'inactive_rvms' => ReverseVendingMachine::where('status', 'inactive')->count(),
            'maintenance_rvms' => ReverseVendingMachine::where('status', 'maintenance')->count(),
            'rvms_with_activity' => $rvmsWithDeposits,
            'utilization_rate' => $totalRVMs > 0 ? ($rvmsWithDeposits / $totalRVMs) * 100 : 0,
        ];
    }

    /**
     * Get trend analytics
     */
    private function getTrendAnalytics(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'daily_deposits' => $this->getDailyTrends('deposits', $startDate, $endDate),
            'daily_rewards' => $this->getDailyTrends('rewards', $startDate, $endDate),
            'daily_users' => $this->getDailyTrends('users', $startDate, $endDate),
        ];
    }

    /**
     * Get daily trends for a specific metric
     */
    private function getDailyTrends(string $metric, Carbon $startDate, Carbon $endDate): array
    {
        $trends = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $dayStart = $current->copy()->startOfDay();
            $dayEnd = $current->copy()->endOfDay();

            $value = match ($metric) {
                'deposits' => Deposit::whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                'rewards' => Deposit::whereBetween('created_at', [$dayStart, $dayEnd])
                    ->where('status', 'completed')->sum('reward_amount'),
                'users' => User::whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                default => 0,
            };

            $trends[] = [
                'date' => $current->toDateString(),
                'value' => $value,
            ];

            $current->addDay();
        }

        return $trends;
    }

    /**
     * Calculate growth rate
     */
    private function calculateGrowthRate(int $previous, int $current): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return (($current - $previous) / $previous) * 100;
    }

    // Additional helper methods for detailed analytics
    private function getDepositsByStatus(Carbon $startDate, Carbon $endDate): array
    {
        return Deposit::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
    }

    private function getDepositsByWasteType(Carbon $startDate, Carbon $endDate): array
    {
        return Deposit::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('cv_waste_type')
            ->selectRaw('cv_waste_type, COUNT(*) as count')
            ->groupBy('cv_waste_type')
            ->get()
            ->pluck('count', 'cv_waste_type')
            ->toArray();
    }

    private function getDepositsByRVM(Carbon $startDate, Carbon $endDate): array
    {
        return Deposit::whereBetween('created_at', [$startDate, $endDate])
            ->with('rvm:id,name')
            ->selectRaw('rvm_id, COUNT(*) as count')
            ->groupBy('rvm_id')
            ->get()
            ->map(function ($item) {
                return [
                    'rvm_id' => $item->rvm_id,
                    'rvm_name' => $item->rvm->name ?? 'Unknown',
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    private function getDailyDepositTrends(Carbon $startDate, Carbon $endDate): array
    {
        return $this->getDailyTrends('deposits', $startDate, $endDate);
    }

    private function getTopDepositUsers(Carbon $startDate, Carbon $endDate): array
    {
        return User::whereHas('deposits', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        })
            ->withCount(['deposits' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->orderBy('deposits_count', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'email'])
            ->toArray();
    }

    private function getTransactionAnalytics(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'total_transactions' => Transaction::whereBetween('created_at', [$startDate, $endDate])->count(),
            'credit_transactions' => Transaction::whereBetween('created_at', [$startDate, $endDate])
                ->where('type', 'credit')->count(),
            'debit_transactions' => Transaction::whereBetween('created_at', [$startDate, $endDate])
                ->where('type', 'debit')->count(),
        ];
    }

    private function getVoucherRedemptionAnalytics(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'total_redemptions' => VoucherRedemption::whereBetween('redeemed_at', [$startDate, $endDate])->count(),
            'used_vouchers' => VoucherRedemption::whereBetween('redeemed_at', [$startDate, $endDate])
                ->whereNotNull('used_at')->count(),
            'unused_vouchers' => VoucherRedemption::whereBetween('redeemed_at', [$startDate, $endDate])
                ->whereNull('used_at')->count(),
        ];
    }

    private function getBalanceDistribution(): array
    {
        return [
            'zero_balance' => User::whereDoesntHave('balance')->orWhereHas('balance', function ($query) {
                $query->where('balance', 0);
            })->count(),
            'low_balance' => User::whereHas('balance', function ($query) {
                $query->whereBetween('balance', [0.01, 1000]);
            })->count(),
            'medium_balance' => User::whereHas('balance', function ($query) {
                $query->whereBetween('balance', [1000.01, 5000]);
            })->count(),
            'high_balance' => User::whereHas('balance', function ($query) {
                $query->where('balance', '>', 5000);
            })->count(),
        ];
    }

    private function getRevenueTrends(Carbon $startDate, Carbon $endDate): array
    {
        return $this->getDailyTrends('rewards', $startDate, $endDate);
    }

    private function getUserRegistrationTrends(Carbon $startDate, Carbon $endDate): array
    {
        return $this->getDailyTrends('users', $startDate, $endDate);
    }

    private function getUserActivityLevels(Carbon $startDate, Carbon $endDate): array
    {
        // Get users with deposit counts in the period
        $usersWithDeposits = User::whereHas('deposits', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        })->withCount(['deposits' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])->get();

        $highlyActive = $usersWithDeposits->where('deposits_count', '>=', 5)->count();
        $moderatelyActive = $usersWithDeposits->where('deposits_count', '>=', 2)->where('deposits_count', '<', 5)->count();
        $lowActivity = $usersWithDeposits->where('deposits_count', 1)->count();
        $inactive = User::whereDoesntHave('deposits', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        })->count();

        return [
            'highly_active' => $highlyActive,
            'moderately_active' => $moderatelyActive,
            'low_activity' => $lowActivity,
            'inactive' => $inactive,
        ];
    }

    private function getUsersByRole(): array
    {
        return User::with('role:id,name')
            ->selectRaw('role_id, COUNT(*) as count')
            ->groupBy('role_id')
            ->get()
            ->map(function ($item) {
                return [
                    'role_id' => $item->role_id,
                    'role_name' => $item->role->name ?? 'No Role',
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    private function getTopContributingUsers(Carbon $startDate, Carbon $endDate): array
    {
        return User::whereHas('deposits', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed');
        })
            ->withSum(['deposits' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'completed');
            }], 'reward_amount')
            ->orderBy('deposits_sum_reward_amount', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'email'])
            ->toArray();
    }

    private function getRVMPerformanceRanking(Carbon $startDate, Carbon $endDate): array
    {
        return ReverseVendingMachine::withCount(['deposits' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])
            ->withSum(['deposits' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'completed');
            }], 'reward_amount')
            ->orderBy('deposits_count', 'desc')
            ->get(['id', 'name', 'status'])
            ->toArray();
    }

    private function getRVMUtilizationRates(Carbon $startDate, Carbon $endDate): array
    {
        return ReverseVendingMachine::withCount(['deposits' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])
            ->get(['id', 'name', 'status'])
            ->map(function ($rvm) {
                return [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'status' => $rvm->status,
                    'deposits_count' => $rvm->deposits_count,
                    'utilization_rate' => $rvm->deposits_count > 0 ? 'High' : 'Low',
                ];
            })
            ->toArray();
    }

    private function getMaintenanceInsights(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'maintenance_rvms' => ReverseVendingMachine::where('status', 'maintenance')->count(),
            'inactive_rvms' => ReverseVendingMachine::where('status', 'inactive')->count(),
            'rvms_needing_attention' => ReverseVendingMachine::where('status', 'maintenance')
                ->orWhere('status', 'inactive')
                ->get(['id', 'name', 'status'])
                ->toArray(),
        ];
    }

    private function generateCustomReport(string $reportType, Carbon $startDate, Carbon $endDate, array $filters): array
    {
        return match ($reportType) {
            'deposits' => $this->getDepositAnalyticsData($startDate, $endDate),
            'economy' => $this->getEconomyAnalyticsData($startDate, $endDate),
            'users' => $this->getUserAnalyticsData($startDate, $endDate),
            'rvms' => $this->getRVMAnalyticsData($startDate, $endDate),
            'comprehensive' => [
                'overview' => $this->getOverviewStats($startDate, $endDate),
                'users' => $this->getUserAnalyticsData($startDate, $endDate),
                'deposits' => $this->getDepositAnalyticsData($startDate, $endDate),
                'economy' => $this->getEconomyAnalyticsData($startDate, $endDate),
                'rvms' => $this->getRVMAnalyticsData($startDate, $endDate),
            ],
            default => [],
        };
    }
}
