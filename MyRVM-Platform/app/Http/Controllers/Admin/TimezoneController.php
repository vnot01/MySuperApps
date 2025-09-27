<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TimezoneController extends Controller
{
    /**
     * Show timezone management dashboard
     */
    public function index(): View
    {
        return view('admin.timezone.index');
    }

    /**
     * Get timezone data for dashboard
     */
    public function getDashboardData(): JsonResponse
    {
        try {
            // Get all device timezones
            $devices = DB::table('device_timezones')
                ->orderBy('last_sync', 'desc')
                ->get();

            // Get recent sync statistics
            $recentSyncs = DB::table('timezone_sync_logs')
                ->where('sync_timestamp', '>=', now()->subDays(7))
                ->orderBy('sync_timestamp', 'desc')
                ->limit(20)
                ->get();

            // Get sync statistics
            $statistics = [
                'total_devices' => $devices->count(),
                'active_devices' => $devices->where('sync_status', 'active')->count(),
                'total_syncs_today' => DB::table('timezone_sync_logs')
                    ->whereDate('sync_timestamp', today())
                    ->count(),
                'total_syncs_week' => DB::table('timezone_sync_logs')
                    ->where('sync_timestamp', '>=', now()->subWeek())
                    ->count(),
                'unique_timezones' => DB::table('timezone_sync_logs')
                    ->distinct('timezone')
                    ->count('timezone')
            ];

            // Get timezone distribution
            $timezoneDistribution = DB::table('timezone_sync_logs')
                ->select('timezone', 'country', DB::raw('COUNT(*) as sync_count'))
                ->where('sync_timestamp', '>=', now()->subDays(30))
                ->groupBy('timezone', 'country')
                ->orderBy('sync_count', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'devices' => $devices,
                    'recent_syncs' => $recentSyncs,
                    'statistics' => $statistics,
                    'timezone_distribution' => $timezoneDistribution
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get timezone dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get device timezone details
     */
    public function getDeviceDetails(string $deviceId): JsonResponse
    {
        try {
            $device = DB::table('device_timezones')
                ->where('device_id', $deviceId)
                ->first();

            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found'
                ], 404);
            }

            // Get sync history
            $syncHistory = DB::table('timezone_sync_logs')
                ->where('device_id', $deviceId)
                ->orderBy('sync_timestamp', 'desc')
                ->limit(50)
                ->get();

            // Get sync statistics for this device
            $deviceStats = [
                'total_syncs' => DB::table('timezone_sync_logs')
                    ->where('device_id', $deviceId)
                    ->count(),
                'auto_syncs' => DB::table('timezone_sync_logs')
                    ->where('device_id', $deviceId)
                    ->where('sync_method', 'automatic')
                    ->count(),
                'manual_syncs' => DB::table('timezone_sync_logs')
                    ->where('device_id', $deviceId)
                    ->where('sync_method', 'manual')
                    ->count(),
                'last_sync' => $device->last_sync,
                'timezone_changes' => DB::table('timezone_sync_logs')
                    ->where('device_id', $deviceId)
                    ->distinct('timezone')
                    ->count('timezone')
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'device' => $device,
                    'sync_history' => $syncHistory,
                    'statistics' => $deviceStats
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get device details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Trigger manual sync for a device
     */
    public function triggerManualSync(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => 'required|string'
        ]);

        try {
            // Log manual sync trigger
            DB::table('timezone_sync_logs')->insert([
                'device_id' => $request->device_id,
                'timezone' => 'manual_trigger',
                'country' => null,
                'city' => null,
                'ip_address' => $request->ip(),
                'sync_method' => 'manual',
                'sync_timestamp' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Manual sync triggered successfully',
                'data' => [
                    'device_id' => $request->device_id,
                    'triggered_at' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to trigger manual sync',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get timezone statistics
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $days = $request->get('days', 30);
            $deviceId = $request->get('device_id');

            $query = DB::table('timezone_sync_logs');
            
            if ($deviceId) {
                $query->where('device_id', $deviceId);
            }

            $query->where('sync_timestamp', '>=', now()->subDays($days));

            $statistics = [
                'total_syncs' => $query->count(),
                'auto_syncs' => $query->clone()->where('sync_method', 'automatic')->count(),
                'manual_syncs' => $query->clone()->where('sync_method', 'manual')->count(),
                'unique_timezones' => $query->clone()->distinct('timezone')->count('timezone'),
                'unique_devices' => $query->clone()->distinct('device_id')->count('device_id'),
                'period_days' => $days
            ];

            // Get daily sync trends
            $dailyTrends = DB::table('timezone_sync_logs')
                ->select(DB::raw('DATE(sync_timestamp) as date'), DB::raw('COUNT(*) as sync_count'))
                ->where('sync_timestamp', '>=', now()->subDays($days))
                ->groupBy(DB::raw('DATE(sync_timestamp)'))
                ->orderBy('date', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'statistics' => $statistics,
                    'daily_trends' => $dailyTrends
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get timezone statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
