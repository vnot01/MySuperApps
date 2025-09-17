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
        $rvms = ReverseVendingMachine::select('id', 'name', 'location_description', 'capacity', 'status', 'updated_at')
            ->get()
            ->map(function ($rvm) {
                // Hitung status berdasarkan kapasitas dan status khusus
                $calculatedStatus = $this->calculateRvmStatus($rvm->capacity, $rvm->status);
                
                return [
                    'id' => $rvm->id,
                    'name' => $rvm->name,
                    'location' => $rvm->location_description, // Menggunakan location_description dari seeder
                    'capacity' => $rvm->capacity ?? 0,
                    'calculated_status' => $calculatedStatus,
                    'last_seen' => $rvm->updated_at ? $rvm->updated_at->format('H:i A') : 'Never',
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

        $timezoneConfig = [
            'timezone' => config('app.timezone', 'Asia/Jakarta'),
            'display_timezone' => 'WIB'
        ];

        return view('admin.dashboard.index', compact('rvms', 'statistics', 'trends', 'timezoneConfig'));
    }

    private function calculateRvmStatus($capacity, $status)
    {
        // Jika ada status khusus (maintenance, inactive, error), gunakan itu
        if (in_array($status, ['maintenance', 'inactive', 'error', 'unknown'])) {
            return $status;
        }
        
        // Hitung status berdasarkan kapasitas
        if ($capacity >= 100) {
            return 'full';
        } elseif ($capacity >= 0) {
            return 'active';
        } else {
            return 'unknown';
        }
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
}
