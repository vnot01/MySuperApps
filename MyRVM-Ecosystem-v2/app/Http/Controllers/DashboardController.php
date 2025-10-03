<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ReverseVendingMachine;
use App\Models\DetectionResult;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get RVM statistics
        $totalRvms = ReverseVendingMachine::count();
        $activeRvms = ReverseVendingMachine::where('status', 'active')->count();
        $maintenanceRvms = ReverseVendingMachine::where('status', 'maintenance')->count();
        $onlineRvms = ReverseVendingMachine::online()->count();

        // Add detection statistics
        $totalDetections = DetectionResult::count();
        $todayDetections = DetectionResult::today()->count();
        $failedDetections = DetectionResult::failed()->count();
        
        // Recent detections with pagination (max 8 per page)
        $detectionsQuery = DetectionResult::with('rvm')->latest('detected_at');
        $totalDetections = $detectionsQuery->count();
        $currentDetectionPage = max(1, (int) $request->get('detection_page', 1));
        $detectionPerPage = 8;
        $totalDetectionPages = ceil($totalDetections / $detectionPerPage);
        
        // Ensure current page doesn't exceed total pages
        $currentDetectionPage = min($currentDetectionPage, $totalDetectionPages);
        
        $recentDetections = $detectionsQuery
            ->skip(($currentDetectionPage - 1) * $detectionPerPage)
            ->take($detectionPerPage)
            ->get()
            ->map(function ($detection) {
                return [
                    'id' => $detection->id,
                    'rvm_name' => $detection->rvm->name,
                    'session_id' => $detection->session_id,
                    'detected_at' => $detection->detected_at->diffForHumans(),
                    'status' => $detection->status,
                    'detection_summary' => $detection->detection_summary
                ];
            });

        // Get RVMs for display with pagination (max 4 per page)
        $rvmsQuery = ReverseVendingMachine::orderBy('name');
        $totalRvms = $rvmsQuery->count();
        $currentPage = max(1, (int) $request->get('rvm_page', 1));
        $perPage = 4;
        $totalPages = ceil($totalRvms / $perPage);
        
        // Ensure current page doesn't exceed total pages
        $currentPage = min($currentPage, $totalPages);
        
        $rvms = $rvmsQuery
            ->skip(($currentPage - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(function ($rvm) {
                $isOnline = $rvm->last_ping && $rvm->last_ping->diffInMinutes(now()) < 5;
                $capacityPercentage = $rvm->capacity > 0 ? round(($rvm->current_load / $rvm->capacity) * 100, 2) : 0;
                
                return [
                    'id' => $rvm->id,
                    'name' => $rvm->name,
                    'location' => $rvm->location,
                    'status' => $rvm->status,
                    'currentLoad' => $rvm->current_load,
                    'capacity' => $rvm->capacity,
                    'isOnline' => $isOnline,
                    'capacityPercentage' => $capacityPercentage,
                    'lastPing' => $rvm->last_ping?->diffForHumans(),
                    'last_ping' => $rvm->last_ping?->toISOString(),
                    'last_connection_check' => $rvm->last_connection_check?->toISOString(),
                    'last_api_check' => $rvm->last_api_check?->toISOString(),
                    'ipAddress' => $rvm->ip_address,
                    'api_key_valid' => $rvm->isApiKeyValid(),
                    'connection_status' => $rvm->connection_status,
                    'api_status' => $rvm->api_status,
                    'comprehensive_status' => $rvm->getComprehensiveStatus()
                ];
            });

        return Inertia::render('Dashboard', [
            'auth' => [
                'user' => $request->user()
            ],
            'rvm_created' => session('rvm_created'),
            'stats' => [
                'totalRvms' => $totalRvms,
                'activeRvms' => $activeRvms,
                'onlineRvms' => $onlineRvms,
                'maintenanceRvms' => $maintenanceRvms,
                'totalCapacity' => 100, // Always 100% for percentage system
                'totalLoad' => round(ReverseVendingMachine::avg('current_load'), 1),
                'averageUsage' => round(ReverseVendingMachine::avg('current_load'), 1),
                'totalDetections' => $totalDetections,
                'todayDetections' => $todayDetections,
                'failedDetections' => $failedDetections
            ],
            'rvms' => $rvms,
            'recentDetections' => $recentDetections,
            'pagination' => [
                'rvms' => [
                    'current_page' => $currentPage,
                    'total_pages' => $totalPages,
                    'per_page' => $perPage,
                    'total' => $totalRvms
                ],
                'detections' => [
                    'current_page' => $currentDetectionPage,
                    'total_pages' => $totalDetectionPages,
                    'per_page' => $detectionPerPage,
                    'total' => $totalDetections
                ]
            ]
        ]);
    }
}
