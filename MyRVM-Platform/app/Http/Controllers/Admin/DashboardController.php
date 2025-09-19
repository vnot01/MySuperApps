<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReverseVendingMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Mengambil data RVM dari database (sesuai dengan seeder)
        $rvms = ReverseVendingMachine::select('id', 'name', 'location', 'location_description', 'capacity', 'status', 'updated_at', 'ip_address', 'port', 'timezone', 'last_timezone_sync', 'connection_status')
            ->get()
            ->map(function ($rvm) {
                // Hitung status berdasarkan kapasitas dan status khusus
                $calculatedStatus = $this->calculateRvmStatus($rvm->capacity, $rvm->status);
                
                // Use location if available, otherwise fallback to location_description
                $location = $rvm->location ?? $rvm->location_description ?? 'Not Set';
                
                return [
                    'id' => $rvm->id,
                    'name' => $rvm->name,
                    'location' => $location,
                    'capacity' => $rvm->capacity ?? 0,
                    'calculated_status' => $calculatedStatus,
                    'last_seen' => $rvm->updated_at ? $rvm->updated_at->format('H:i A') : 'Never',
                    'ip_address' => $rvm->ip_address ?? 'Not Set',
                    'port' => $rvm->port ?? 8000,
                    'timezone' => $rvm->timezone ?? 'Not Set',
                    'last_timezone_sync' => $rvm->last_timezone_sync,
                    'connection_status' => $rvm->connection_status ?? 'unknown',
                    'processing_engines' => [] // Sementara kosong, bisa ditambahkan nanti
                ];
            });

        // Hitung statistik dari database
        $statistics = [
            'total_rvm' => ReverseVendingMachine::count(),
            'active_sessions' => $this->getActiveSessionsCount(),
            'deposits_today' => $this->getDepositsTodayCount(),
            'total_issues' => $this->getTotalIssuesCount()
        ];

        // Hitung trend 30 hari berdasarkan data historis
        $trends = [
            'total_rvm_trend' => $this->calculateTrendWithDirection(
                $statistics['total_rvm'],
                $this->getTotalRvmCount30DaysAgo()
            ),
            'active_sessions_trend' => $this->calculateTrendWithDirection(
                $statistics['active_sessions'],
                $this->getActiveSessionsCount30DaysAgo()
            ),
            'deposits_today_trend' => $this->calculateTrendWithDirection(
                $statistics['deposits_today'],
                $this->getDepositsTodayCount30DaysAgo()
            ),
            'total_issues_trend' => $this->calculateTrendWithDirection(
                $statistics['total_issues'],
                $this->getTotalIssuesCount30DaysAgo()
            )
        ];

        // Get timezone sync data for RVM monitoring
        $timezoneData = $this->getTimezoneSyncData();
        
        $timezoneConfig = [
            'timezone' => config('app.timezone', 'Asia/Jakarta'),
            'display_timezone' => 'WIB'
        ];

        return view('admin.dashboard.index', compact('rvms', 'statistics', 'trends', 'timezoneConfig', 'timezoneData'));
    }

    private function calculateRvmStatus($capacity, $status)
    {
        // Prioritize database status over capacity-based calculation
        // Only override if status is 'active' and capacity is 100%
        if ($status === 'active' && $capacity >= 100) {
            return 'full';
        }
        
        // Use database status as primary source
        return $status;
    }

    private function getActiveSessionsCount()
    {
        // Hitung RVM yang aktif (status active dan kapasitas < 100)
        return ReverseVendingMachine::where('status', 'active')
            ->where('capacity', '<', 100)
            ->count();
    }

    private function getDepositsTodayCount()
    {
        // Hitung deposit hari ini dari tabel deposits
        return \App\Models\Deposit::whereDate('created_at', today())
            ->where('status', 'completed')
            ->count();
    }

    private function getTotalIssuesCount()
    {
        // Hitung total issues berdasarkan status dari seeder
        return ReverseVendingMachine::whereIn('status', ['error', 'maintenance', 'inactive'])
            ->count();
    }

    /**
     * Calculate trend percentage between current and previous period
     */
    private function calculateTrend($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 'N/A' : '0%';
        }
        
        $trend = (($current - $previous) / $previous) * 100;
        return round($trend, 1) . '%';
    }

    /**
     * Calculate trend with direction information
     */
    private function calculateTrendWithDirection($current, $previous)
    {
        if ($previous == 0) {
            return [
                'percentage' => $current > 0 ? 'N/A' : '0%',
                'direction' => $current > 0 ? 'up' : 'neutral',
                'value' => $current - $previous
            ];
        }
        
        $trend = (($current - $previous) / $previous) * 100;
        $direction = $trend > 0 ? 'up' : ($trend < 0 ? 'down' : 'neutral');
        
        return [
            'percentage' => round($trend, 1) . '%',
            'direction' => $direction,
            'value' => $current - $previous
        ];
    }

    /**
     * Get Total RVM count 30 days ago
     */
    private function getTotalRvmCount30DaysAgo()
    {
        return ReverseVendingMachine::where('created_at', '<=', now()->subDays(30))
            ->count();
    }

    /**
     * Get Active Sessions count 30 days ago
     */
    private function getActiveSessionsCount30DaysAgo()
    {
        // Use RVM active count 30 days ago instead of sessions
        return ReverseVendingMachine::where('status', 'active')
            ->where('capacity', '<', 100)
            ->where('updated_at', '<=', now()->subDays(30))
            ->count();
    }

    /**
     * Get Deposits count 30 days ago
     */
    private function getDepositsTodayCount30DaysAgo()
    {
        $date30DaysAgo = now()->subDays(30)->format('Y-m-d');
        return \App\Models\Deposit::whereDate('created_at', $date30DaysAgo)
            ->where('status', 'completed')
            ->count();
    }

    /**
     * Get Total Issues count 30 days ago
     */
    private function getTotalIssuesCount30DaysAgo()
    {
        return ReverseVendingMachine::whereIn('status', ['error', 'maintenance', 'inactive'])
            ->where('updated_at', '<=', now()->subDays(30))
            ->count();
    }

    private function getTimezoneSyncData()
    {
        try {
            // Get device timezone data
            $deviceTimezones = DB::table('device_timezones')
                ->orderBy('last_sync', 'desc')
                ->get();

            // Get recent sync statistics
            $syncStats = [
                'total_devices' => $deviceTimezones->count(),
                'active_devices' => $deviceTimezones->where('sync_status', 'active')->count(),
                'syncs_today' => DB::table('timezone_sync_logs')
                    ->whereDate('sync_timestamp', today())
                    ->count(),
                'unique_timezones' => DB::table('timezone_sync_logs')
                    ->distinct('timezone')
                    ->count('timezone')
            ];

            // Get recent sync activity
            $recentSyncs = DB::table('timezone_sync_logs')
                ->where('sync_timestamp', '>=', now()->subHours(24))
                ->orderBy('sync_timestamp', 'desc')
                ->limit(5)
                ->get();

            return [
                'devices' => $deviceTimezones,
                'statistics' => $syncStats,
                'recent_syncs' => $recentSyncs
            ];

        } catch (\Exception $e) {
            // Return empty data if timezone tables don't exist yet
            return [
                'devices' => collect(),
                'statistics' => [
                    'total_devices' => 0,
                    'active_devices' => 0,
                    'syncs_today' => 0,
                    'unique_timezones' => 0
                ],
                'recent_syncs' => collect()
            ];
        }
    }
}
