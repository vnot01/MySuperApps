<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\UserBalance;
use App\Models\Transaction;
use App\Services\EconomyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BalanceController extends Controller
{
    protected EconomyService $economyService;

    public function __construct(EconomyService $economyService)
    {
        $this->economyService = $economyService;
    }
    /**
     * Get user's current balance
     *
     * @return JsonResponse
     */
    public function getBalance(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Get or create user balance
            $userBalance = UserBalance::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'currency' => 'IDR']
            );

            // Get recent transactions (last 10)
            $recentTransactions = Transaction::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Calculate statistics
            $totalCredits = Transaction::where('user_id', $user->id)
                ->where('type', 'credit')
                ->sum('amount');

            $totalDebits = Transaction::where('user_id', $user->id)
                ->where('type', 'debit')
                ->sum('amount');

            $totalTransactions = Transaction::where('user_id', $user->id)->count();

            return response()->json([
                'success' => true,
                'message' => 'User balance retrieved successfully',
                'data' => [
                    'user_id' => $user->id,
                    'current_balance' => $userBalance->balance,
                    'currency' => $userBalance->currency,
                    'statistics' => [
                        'total_credits' => $totalCredits,
                        'total_debits' => $totalDebits,
                        'total_transactions' => $totalTransactions,
                        'net_balance' => $totalCredits - $totalDebits
                    ],
                    'recent_transactions' => $recentTransactions->map(function ($transaction) {
                        return [
                            'id' => $transaction->id,
                            'type' => $transaction->type,
                            'amount' => $transaction->amount,
                            'balance_before' => $transaction->balance_before,
                            'balance_after' => $transaction->balance_after,
                            'description' => $transaction->description,
                            'source_type' => $transaction->sourceable_type,
                            'source_id' => $transaction->sourceable_id,
                            'created_at' => $transaction->created_at
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user balance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's transaction history
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTransactionHistory(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->get('per_page', 15);
            $type = $request->get('type'); // credit, debit, or null for all
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $query = Transaction::where('user_id', $user->id);

            // Filter by type
            if ($type && in_array($type, ['credit', 'debit'])) {
                $query->where('type', $type);
            }

            // Filter by date range
            if ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->where('created_at', '<=', $endDate);
            }

            $transactions = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Transaction history retrieved successfully',
                'data' => $transactions->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'type' => $transaction->type,
                        'amount' => $transaction->amount,
                        'balance_before' => $transaction->balance_before,
                        'balance_after' => $transaction->balance_after,
                        'description' => $transaction->description,
                        'source_type' => $transaction->sourceable_type,
                        'source_id' => $transaction->sourceable_id,
                        'created_at' => $transaction->created_at
                    ];
                }),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                    'last_page' => $transactions->lastPage(),
                    'from' => $transactions->firstItem(),
                    'to' => $transactions->lastItem()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve transaction history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's balance statistics
     *
     * @return JsonResponse
     */
    public function getBalanceStatistics(): JsonResponse
    {
        try {
            $user = Auth::user();

            // Get current balance
            $userBalance = UserBalance::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'currency' => 'IDR']
            );

            // Get statistics for last 30 days
            $thirtyDaysAgo = now()->subDays(30);

            $stats = Transaction::where('user_id', $user->id)
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->selectRaw('
                    COUNT(*) as total_transactions,
                    SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as total_credits,
                    SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as total_debits,
                    COUNT(CASE WHEN type = ? THEN 1 END) as credit_count,
                    COUNT(CASE WHEN type = ? THEN 1 END) as debit_count
                ', ['credit', 'debit', 'credit', 'debit'])
                ->first();

            // Get daily balance changes for chart
            $dailyChanges = Transaction::where('user_id', $user->id)
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->selectRaw('
                    DATE(created_at) as date,
                    SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as daily_credits,
                    SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as daily_debits
                ', ['credit', 'debit'])
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Balance statistics retrieved successfully',
                'data' => [
                    'current_balance' => $userBalance->balance,
                    'currency' => $userBalance->currency,
                    'last_30_days' => [
                        'total_transactions' => $stats->total_transactions ?? 0,
                        'total_credits' => $stats->total_credits ?? 0,
                        'total_debits' => $stats->total_debits ?? 0,
                        'credit_count' => $stats->credit_count ?? 0,
                        'debit_count' => $stats->debit_count ?? 0,
                        'net_change' => ($stats->total_credits ?? 0) - ($stats->total_debits ?? 0)
                    ],
                    'daily_changes' => $dailyChanges->map(function ($change) {
                        return [
                            'date' => $change->date,
                            'credits' => $change->daily_credits,
                            'debits' => $change->daily_debits,
                            'net_change' => $change->daily_credits - $change->daily_debits
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve balance statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's economy summary
     *
     * @return JsonResponse
     */
    public function getEconomySummary(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $result = $this->economyService->getUserEconomySummary($user->id);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Economy summary retrieved successfully',
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve economy summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
