<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReverseVendingMachine;
use App\Models\DetectionResult;
use App\Models\Transaction;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function getDashboardAnalytics(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', '7d'); // 1d, 7d, 30d, 90d
            $startDate = $this->getStartDate($period);
            
            $analytics = [
                'period' => $period,
                'date_range' => [
                    'start' => $startDate->toISOString(),
                    'end' => now()->toISOString()
                ],
                'overview' => $this->getOverviewMetrics($startDate),
                'rvm_performance' => $this->getRvmPerformanceMetrics($startDate),
                'detection_analytics' => $this->getDetectionAnalytics($startDate),
                'economy_analytics' => $this->getEconomyAnalytics($startDate),
                'trends' => $this->getTrendsData($startDate),
                'alerts' => $this->getSystemAlerts($startDate)
            ];

            return response()->json([
                'success' => true,
                'analytics' => $analytics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getRvmAnalytics(Request $request, $rvmId): JsonResponse
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($rvmId);
            $period = $request->get('period', '7d');
            $startDate = $this->getStartDate($period);

            $analytics = [
                'rvm_id' => $rvmId,
                'rvm_name' => $rvm->name,
                'period' => $period,
                'date_range' => [
                    'start' => $startDate->toISOString(),
                    'end' => now()->toISOString()
                ],
                'performance' => $this->getRvmPerformance($rvmId, $startDate),
                'detections' => $this->getRvmDetectionAnalytics($rvmId, $startDate),
                'economy' => $this->getRvmEconomyAnalytics($rvmId, $startDate),
                'health' => $this->getRvmHealthMetrics($rvmId),
                'trends' => $this->getRvmTrends($rvmId, $startDate)
            ];

            return response()->json([
                'success' => true,
                'analytics' => $analytics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getDetectionAnalytics(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', '7d');
            $startDate = $this->getStartDate($period);

            $analytics = [
                'period' => $period,
                'date_range' => [
                    'start' => $startDate->toISOString(),
                    'end' => now()->toISOString()
                ],
                'summary' => $this->getDetectionSummary($startDate),
                'by_class' => $this->getDetectionByClass($startDate),
                'by_rvm' => $this->getDetectionByRvm($startDate),
                'accuracy_trends' => $this->getAccuracyTrends($startDate),
                'processing_times' => $this->getProcessingTimeAnalytics($startDate)
            ];

            return response()->json([
                'success' => true,
                'analytics' => $analytics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getStartDate(string $period): Carbon
    {
        return match ($period) {
            '1d' => now()->subDay(),
            '7d' => now()->subWeek(),
            '30d' => now()->subMonth(),
            '90d' => now()->subMonths(3),
            default => now()->subWeek()
        };
    }

    private function getOverviewMetrics(Carbon $startDate): array
    {
        return [
            'total_rvms' => ReverseVendingMachine::count(),
            'active_rvms' => ReverseVendingMachine::where('status', 'active')->count(),
            'online_rvms' => ReverseVendingMachine::where('last_ping', '>=', now()->subMinutes(5))->count(),
            'total_detections' => DetectionResult::where('created_at', '>=', $startDate)->count(),
            'total_deposits' => Deposit::where('created_at', '>=', $startDate)->count(),
            'total_rewards' => Transaction::where('type', 'credit')
                ->where('created_at', '>=', $startDate)
                ->sum('amount'),
            'average_processing_time' => DetectionResult::where('created_at', '>=', $startDate)
                ->avg('processing_time_ms') ?? 0
        ];
    }

    private function getRvmPerformanceMetrics(Carbon $startDate): array
    {
        return ReverseVendingMachine::withCount(['detectionResults' => function ($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }])
        ->withCount(['deposits' => function ($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }])
        ->get()
        ->map(function ($rvm) {
            return [
                'id' => $rvm->id,
                'name' => $rvm->name,
                'location' => $rvm->location,
                'status' => $rvm->status,
                'detections_count' => $rvm->detection_results_count,
                'deposits_count' => $rvm->deposits_count,
                'last_activity' => $rvm->last_ping,
                'is_online' => $rvm->is_online
            ];
        });
    }

    private function getDetectionAnalytics(Carbon $startDate): array
    {
        $detections = DetectionResult::where('created_at', '>=', $startDate)->get();
        
        return [
            'total_detections' => $detections->count(),
            'successful_detections' => $detections->where('status', 'completed')->count(),
            'failed_detections' => $detections->where('status', 'failed')->count(),
            'average_confidence' => $detections->avg('confidence') ?? 0,
            'by_hour' => $this->getDetectionsByHour($detections),
            'by_day' => $this->getDetectionsByDay($detections)
        ];
    }

    private function getEconomyAnalytics(Carbon $startDate): array
    {
        return [
            'total_rewards' => Transaction::where('type', 'credit')
                ->where('created_at', '>=', $startDate)
                ->sum('amount'),
            'total_vouchers_used' => \App\Models\VoucherRedemption::where('created_at', '>=', $startDate)->count(),
            'average_reward_per_detection' => $this->getAverageRewardPerDetection($startDate),
            'top_waste_types' => $this->getTopWasteTypes($startDate),
            'revenue_trends' => $this->getRevenueTrends($startDate)
        ];
    }

    private function getTrendsData(Carbon $startDate): array
    {
        return [
            'detections_trend' => $this->getDetectionsTrend($startDate),
            'revenue_trend' => $this->getRevenueTrend($startDate),
            'rvm_activity_trend' => $this->getRvmActivityTrend($startDate)
        ];
    }

    private function getSystemAlerts(Carbon $startDate): array
    {
        // This would typically come from a monitoring system
        return [
            'high_error_rate' => false,
            'low_detection_accuracy' => false,
            'rvm_offline' => ReverseVendingMachine::where('last_ping', '<', now()->subMinutes(10))->count(),
            'system_overload' => false
        ];
    }

    private function getRvmPerformance(int $rvmId, Carbon $startDate): array
    {
        $rvm = ReverseVendingMachine::findOrFail($rvmId);
        
        return [
            'uptime_percentage' => $this->calculateUptimePercentage($rvmId, $startDate),
            'detections_per_hour' => $this->getDetectionsPerHour($rvmId, $startDate),
            'average_processing_time' => $this->getAverageProcessingTime($rvmId, $startDate),
            'error_rate' => $this->getErrorRate($rvmId, $startDate),
            'capacity_utilization' => $rvm->capacity_percentage
        ];
    }

    private function getRvmDetectionAnalytics(int $rvmId, Carbon $startDate): array
    {
        $detections = DetectionResult::where('rvm_id', $rvmId)
            ->where('created_at', '>=', $startDate)
            ->get();

        return [
            'total_detections' => $detections->count(),
            'successful_detections' => $detections->where('status', 'completed')->count(),
            'failed_detections' => $detections->where('status', 'failed')->count(),
            'average_confidence' => $detections->avg('confidence') ?? 0,
            'by_class' => $this->getDetectionsByClassForRvm($rvmId, $startDate),
            'hourly_distribution' => $this->getHourlyDistribution($detections)
        ];
    }

    private function getRvmEconomyAnalytics(int $rvmId, Carbon $startDate): array
    {
        $deposits = Deposit::where('rvm_id', $rvmId)
            ->where('created_at', '>=', $startDate)
            ->get();

        return [
            'total_deposits' => $deposits->count(),
            'total_rewards' => $deposits->sum('reward_amount'),
            'average_reward' => $deposits->avg('reward_amount') ?? 0,
            'by_waste_type' => $this->getRewardsByWasteType($deposits),
            'daily_revenue' => $this->getDailyRevenue($deposits)
        ];
    }

    private function getRvmHealthMetrics(int $rvmId): array
    {
        $rvm = ReverseVendingMachine::findOrFail($rvmId);
        
        return [
            'status' => $rvm->status,
            'is_online' => $rvm->is_online,
            'last_ping' => $rvm->last_ping,
            'connection_status' => $rvm->connection_status,
            'api_status' => $rvm->api_status,
            'capacity_percentage' => $rvm->capacity_percentage,
            'current_load' => $rvm->current_load,
            'max_capacity' => $rvm->capacity
        ];
    }

    private function getRvmTrends(int $rvmId, Carbon $startDate): array
    {
        return [
            'detections_trend' => $this->getRvmDetectionsTrend($rvmId, $startDate),
            'revenue_trend' => $this->getRvmRevenueTrend($rvmId, $startDate),
            'accuracy_trend' => $this->getRvmAccuracyTrend($rvmId, $startDate)
        ];
    }

    // Helper methods for specific analytics calculations
    private function getDetectionsByHour($detections): array
    {
        return $detections->groupBy(function ($detection) {
            return $detection->created_at->format('H');
        })->map->count()->toArray();
    }

    private function getDetectionsByDay($detections): array
    {
        return $detections->groupBy(function ($detection) {
            return $detection->created_at->format('Y-m-d');
        })->map->count()->toArray();
    }

    private function getAverageRewardPerDetection(Carbon $startDate): float
    {
        $totalRewards = Transaction::where('type', 'credit')
            ->where('created_at', '>=', $startDate)
            ->sum('amount');
        
        $totalDetections = DetectionResult::where('created_at', '>=', $startDate)->count();
        
        return $totalDetections > 0 ? $totalRewards / $totalDetections : 0;
    }

    private function getTopWasteTypes(Carbon $startDate): array
    {
        return Deposit::where('created_at', '>=', $startDate)
            ->select('waste_type', DB::raw('count(*) as count'))
            ->groupBy('waste_type')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    private function getRevenueTrends(Carbon $startDate): array
    {
        return Transaction::where('type', 'credit')
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getDetectionsTrend(Carbon $startDate): array
    {
        return DetectionResult::where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getRevenueTrend(Carbon $startDate): array
    {
        return $this->getRevenueTrends($startDate);
    }

    private function getRvmActivityTrend(Carbon $startDate): array
    {
        return DetectionResult::where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function calculateUptimePercentage(int $rvmId, Carbon $startDate): float
    {
        $totalMinutes = $startDate->diffInMinutes(now());
        $offlineMinutes = 0; // This would be calculated based on actual offline periods
        
        return $totalMinutes > 0 ? (($totalMinutes - $offlineMinutes) / $totalMinutes) * 100 : 0;
    }

    private function getDetectionsPerHour(int $rvmId, Carbon $startDate): float
    {
        $totalDetections = DetectionResult::where('rvm_id', $rvmId)
            ->where('created_at', '>=', $startDate)
            ->count();
        
        $totalHours = $startDate->diffInHours(now());
        
        return $totalHours > 0 ? $totalDetections / $totalHours : 0;
    }

    private function getAverageProcessingTime(int $rvmId, Carbon $startDate): float
    {
        return DetectionResult::where('rvm_id', $rvmId)
            ->where('created_at', '>=', $startDate)
            ->avg('processing_time_ms') ?? 0;
    }

    private function getErrorRate(int $rvmId, Carbon $startDate): float
    {
        $totalDetections = DetectionResult::where('rvm_id', $rvmId)
            ->where('created_at', '>=', $startDate)
            ->count();
        
        $failedDetections = DetectionResult::where('rvm_id', $rvmId)
            ->where('created_at', '>=', $startDate)
            ->where('status', 'failed')
            ->count();
        
        return $totalDetections > 0 ? ($failedDetections / $totalDetections) * 100 : 0;
    }

    private function getDetectionsByClassForRvm(int $rvmId, Carbon $startDate): array
    {
        return DetectionResult::where('rvm_id', $rvmId)
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('JSON_EXTRACT(detection_data, "$.class_name") as class_name'), DB::raw('COUNT(*) as count'))
            ->groupBy('class_name')
            ->orderBy('count', 'desc')
            ->get()
            ->toArray();
    }

    private function getHourlyDistribution($detections): array
    {
        return $detections->groupBy(function ($detection) {
            return $detection->created_at->format('H');
        })->map->count()->toArray();
    }

    private function getRewardsByWasteType($deposits): array
    {
        return $deposits->groupBy('waste_type')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total_reward' => $group->sum('reward_amount'),
                    'average_reward' => $group->avg('reward_amount')
                ];
            })
            ->toArray();
    }

    private function getDailyRevenue($deposits): array
    {
        return $deposits->groupBy(function ($deposit) {
            return $deposit->created_at->format('Y-m-d');
        })->map(function ($group) {
            return $group->sum('reward_amount');
        })->toArray();
    }

    private function getRvmDetectionsTrend(int $rvmId, Carbon $startDate): array
    {
        return DetectionResult::where('rvm_id', $rvmId)
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getRvmRevenueTrend(int $rvmId, Carbon $startDate): array
    {
        return Deposit::where('rvm_id', $rvmId)
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(reward_amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getRvmAccuracyTrend(int $rvmId, Carbon $startDate): array
    {
        return DetectionResult::where('rvm_id', $rvmId)
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('AVG(confidence) as accuracy'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getDetectionSummary(Carbon $startDate): array
    {
        $detections = DetectionResult::where('created_at', '>=', $startDate)->get();
        
        return [
            'total' => $detections->count(),
            'completed' => $detections->where('status', 'completed')->count(),
            'failed' => $detections->where('status', 'failed')->count(),
            'pending' => $detections->where('status', 'pending')->count(),
            'processing' => $detections->where('status', 'processing')->count()
        ];
    }

    private function getDetectionByClass(Carbon $startDate): array
    {
        return DetectionResult::where('created_at', '>=', $startDate)
            ->select(DB::raw('JSON_EXTRACT(detection_data, "$.class_name") as class_name'), DB::raw('COUNT(*) as count'))
            ->groupBy('class_name')
            ->orderBy('count', 'desc')
            ->get()
            ->toArray();
    }

    private function getDetectionByRvm(Carbon $startDate): array
    {
        return DetectionResult::with('rvm')
            ->where('created_at', '>=', $startDate)
            ->select('rvm_id', DB::raw('COUNT(*) as count'))
            ->groupBy('rvm_id')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'rvm_id' => $item->rvm_id,
                    'rvm_name' => $item->rvm->name ?? 'Unknown',
                    'count' => $item->count
                ];
            })
            ->toArray();
    }

    private function getAccuracyTrends(Carbon $startDate): array
    {
        return DetectionResult::where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('AVG(confidence) as accuracy'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getProcessingTimeAnalytics(Carbon $startDate): array
    {
        $detections = DetectionResult::where('created_at', '>=', $startDate)->get();
        
        return [
            'average' => $detections->avg('processing_time_ms') ?? 0,
            'min' => $detections->min('processing_time_ms') ?? 0,
            'max' => $detections->max('processing_time_ms') ?? 0,
            'median' => $detections->median('processing_time_ms') ?? 0
        ];
    }
}
