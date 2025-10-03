<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TimezoneController extends Controller
{
    /**
     * Send timezone sync information from Jetson Orin
     */
    public function sync(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string|max:100',
            'timezone' => 'required|string|max:50',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'ip' => 'nullable|ip',
            'timestamp' => 'required|date',
            'sync_method' => 'required|in:automatic,manual'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Log timezone sync event
            $syncLog = DB::table('timezone_sync_logs')->insertGetId([
                'device_id' => $request->device_id,
                'timezone' => $request->timezone,
                'country' => $request->country,
                'city' => $request->city,
                'ip_address' => $request->ip,
                'sync_method' => $request->sync_method,
                'sync_timestamp' => Carbon::parse($request->timestamp),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update or create device timezone record
            DB::table('device_timezones')->updateOrInsert(
                ['device_id' => $request->device_id],
                [
                    'current_timezone' => $request->timezone,
                    'country' => $request->country,
                    'city' => $request->city,
                    'last_sync' => Carbon::parse($request->timestamp),
                    'sync_status' => 'active',
                    'updated_at' => now()
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Timezone sync recorded successfully',
                'data' => [
                    'sync_log_id' => $syncLog,
                    'device_id' => $request->device_id,
                    'timezone' => $request->timezone,
                    'sync_method' => $request->sync_method,
                    'timestamp' => $request->timestamp
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to record timezone sync',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get timezone status for a device
     */
    public function getStatus(string $deviceId): JsonResponse
    {
        try {
            $deviceTimezone = DB::table('device_timezones')
                ->where('device_id', $deviceId)
                ->first();

            if (!$deviceTimezone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device timezone not found'
                ], 404);
            }

            // Get recent sync history
            $recentSyncs = DB::table('timezone_sync_logs')
                ->where('device_id', $deviceId)
                ->orderBy('sync_timestamp', 'desc')
                ->limit(5)
                ->get();

            // Calculate local time
            $timezone = $deviceTimezone->current_timezone;
            $localTime = Carbon::now($timezone)->format('Y-m-d H:i:s');
            $utcTime = Carbon::now('UTC')->format('Y-m-d H:i:s');
            $timezoneOffset = Carbon::now($timezone)->format('P');

            return response()->json([
                'success' => true,
                'data' => [
                    'device_id' => $deviceId,
                    'current_timezone' => $timezone,
                    'country' => $deviceTimezone->country,
                    'city' => $deviceTimezone->city,
                    'local_time' => $localTime,
                    'utc_time' => $utcTime,
                    'timezone_offset' => $timezoneOffset,
                    'last_sync' => $deviceTimezone->last_sync,
                    'sync_status' => $deviceTimezone->sync_status,
                    'recent_syncs' => $recentSyncs
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get timezone status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manual sync trigger from dashboard
     */
    public function manualSync(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string|max:100',
            'trigger' => 'required|string|in:dashboard_button,api_call,admin_action'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

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

            // Get current device timezone info
            $deviceTimezone = DB::table('device_timezones')
                ->where('device_id', $request->device_id)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Manual sync triggered successfully',
                'data' => [
                    'device_id' => $request->device_id,
                    'trigger' => $request->trigger,
                    'current_timezone' => $deviceTimezone ? $deviceTimezone->current_timezone : null,
                    'triggered_at' => now()->toISOString(),
                    'status' => 'sync_requested'
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
     * Get timezone sync statistics
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $deviceId = $request->get('device_id');
            $days = $request->get('days', 30);

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

            // Get timezone distribution
            $timezoneDistribution = $query->clone()
                ->select('timezone', 'country', DB::raw('COUNT(*) as sync_count'))
                ->groupBy('timezone', 'country')
                ->orderBy('sync_count', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'statistics' => $statistics,
                    'timezone_distribution' => $timezoneDistribution
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

    /**
     * Get all device timezones
     */
    public function getAllDevices(): JsonResponse
    {
        try {
            $devices = DB::table('device_timezones')
                ->orderBy('last_sync', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'devices' => $devices,
                    'total_devices' => $devices->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get device timezones',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
