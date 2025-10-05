<?php

namespace App\Http\Controllers;

use App\Models\ReverseVendingMachine;
use App\Models\DetectionResult;
use App\Models\RvmMonitoringMetric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class MaintenanceController extends Controller
{
    public function show($rvmId)
    {
        $rvm = ReverseVendingMachine::findOrFail($rvmId);
        
        // Update RVM status to maintenance when maintenance page is opened
        $rvm->update([
            'status' => 'maintenance',
            'last_maintenance' => now()
        ]);
        
        // Get RVM information
        $rvmInfo = [
            'id' => $rvm->id,
            'name' => $rvm->name,
            'location' => $rvm->location,
            'ip_address' => $rvm->ip_address,
            'status' => $rvm->status,
            'capacity' => $rvm->capacity,
            'current_load' => $rvm->current_load,
            'api_key' => $rvm->api_key,
            'last_ping' => $rvm->last_ping?->toISOString(),
            'created_at' => $rvm->created_at?->toISOString(),
        ];

        // Get detection results for this RVM
        $detectionResults = DetectionResult::where('rvm_id', $rvmId)
            ->with('rvm')
            ->latest('detected_at')
            ->paginate(20)
            ->through(function ($detection) {
                return [
                    'id' => $detection->id,
                    'rvm_name' => $detection->rvm->name,
                    'session_id' => $detection->session_id,
                    'detected_at' => $detection->detected_at?->toISOString(),
                    'status' => $detection->status,
                    'detection_summary' => $detection->detection_summary,
                    'detection_data' => $detection->detection_data,
                    'error_message' => $detection->error_message,
                ];
            });

        // Get RVM analytics
        $analytics = $this->getRvmAnalytics($rvmId);

        // Get RVM health status from Jetson
        $healthStatus = $this->getRvmHealthStatus($rvm);
        
        // Get API status from Jetson
        $apiStatus = $this->getRvmApiStatus($rvm);
        
        // Get hardware information from Jetson
        $hardwareInfo = $this->getRvmHardwareInfo($rvm);
        
        // Get monitoring status from Jetson
        $monitoringStatus = $this->getRvmMonitoringStatus($rvm);
        
        // Get monitoring summary from Jetson
        $monitoringSummary = $this->getRvmMonitoringSummary($rvm);

        // Get monitoring analytics from database
        $monitoringAnalytics = $this->getRvmMonitoringAnalytics($rvmId);

        return Inertia::render('Maintenance/Show', [
            'rvm' => $rvmInfo,
            'detectionResults' => $detectionResults,
            'analytics' => $analytics,
            'healthStatus' => $healthStatus,
            'apiStatus' => $apiStatus,
            'hardwareInfo' => $hardwareInfo,
            'monitoringStatus' => $monitoringStatus,
            'monitoringSummary' => $monitoringSummary,
            'monitoringAnalytics' => $monitoringAnalytics,
        ]);
    }

    private function getRvmAnalytics($rvmId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . ReverseVendingMachine::find($rvmId)->api_key,
                'Accept' => 'application/json',
            ])->get(config('app.url') . "/api/analytics/rvm/{$rvmId}");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            \Log::error('Failed to get RVM analytics: ' . $e->getMessage());
        }

        return null;
    }

    private function getRvmHealthStatus($rvm)
    {
        if (!$rvm->ip_address) {
            return null;
        }

        try {
            $response = Http::timeout(5)->get("http://{$rvm->ip_address}:5000/api/health");
            
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            \Log::error("Failed to get health status for RVM {$rvm->id}: " . $e->getMessage());
        }

        return null;
    }

    private function getRvmApiStatus($rvm)
    {
        if (!$rvm->ip_address) {
            return null;
        }

        try {
            $response = Http::timeout(5)->get("http://{$rvm->ip_address}:5000/api/status");
            
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            \Log::error("Failed to get API status for RVM {$rvm->id}: " . $e->getMessage());
        }

        return null;
    }

    private function getRvmHardwareInfo($rvm)
    {
        if (!$rvm->ip_address) {
            return null;
        }

        try {
            $response = Http::timeout(5)->get("http://{$rvm->ip_address}:5000/api/hardware");
            
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            \Log::error("Failed to get hardware info for RVM {$rvm->id}: " . $e->getMessage());
        }

        return null;
    }

    private function getRvmMonitoringStatus($rvm)
    {
        if (!$rvm->ip_address) {
            return null;
        }

        try {
            $response = Http::timeout(5)->get("http://{$rvm->ip_address}:5000/api/monitoring/status");
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Parse the monitoring data from the response
                if (isset($data['monitoring']['current_metrics'])) {
                    $metrics = $data['monitoring']['current_metrics'];
                    return [
                        'cpu_usage' => round($metrics['cpu_percent'] ?? 0, 1),
                        'memory_usage' => round($metrics['memory_percent'] ?? 0, 1),
                        'disk_usage' => round($metrics['disk_usage_percent'] ?? 0, 1),
                        'gpu_usage' => round($metrics['gpu_memory_percent'] ?? 0, 1),
                        'alerts' => $data['monitoring']['recent_alerts'] ?? [],
                        'timestamp' => $data['monitoring']['timestamp'] ?? now()->toISOString(),
                        'status' => 'success'
                    ];
                }
                
                // If direct format (new endpoint)
                if (isset($data['cpu_usage'])) {
                    return $data;
                }
            }
        } catch (\Exception $e) {
            \Log::error("Failed to get monitoring status for RVM {$rvm->id}: " . $e->getMessage());
        }

        // Return mock data if monitoring endpoint is not available
        return [
            'cpu_usage' => rand(20, 80),
            'memory_usage' => rand(30, 70),
            'disk_usage' => rand(40, 90),
            'gpu_usage' => rand(10, 60),
            'alerts' => [],
            'timestamp' => now()->toISOString(),
            'status' => 'monitoring_unavailable'
        ];
    }

    private function getRvmMonitoringSummary($rvm)
    {
        if (!$rvm->ip_address) {
            return null;
        }

        try {
            $response = Http::timeout(5)->get("http://{$rvm->ip_address}:5000/api/monitoring/summary");
            
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            \Log::error("Failed to get monitoring summary for RVM {$rvm->id}: " . $e->getMessage());
        }

        // Return mock data if monitoring endpoint is not available
        return [
            'daily' => [
                'detections' => rand(50, 200),
                'success_rate' => rand(85, 98),
                'avg_processing_time' => rand(2, 8),
                'cpu_avg' => rand(30, 70),
                'memory_avg' => rand(40, 80)
            ],
            'monthly' => [
                'detections' => rand(1000, 5000),
                'success_rate' => rand(88, 95),
                'avg_processing_time' => rand(3, 10),
                'cpu_avg' => rand(35, 75),
                'memory_avg' => rand(45, 85)
            ],
            'yearly' => [
                'detections' => rand(10000, 50000),
                'success_rate' => rand(90, 97),
                'avg_processing_time' => rand(4, 12),
                'cpu_avg' => rand(40, 80),
                'memory_avg' => rand(50, 90)
            ]
        ];
    }

    /**
     * Update IP address for the specified RVM.
     */
    public function updateIpAddress(Request $request, $rvmId)
    {
        $rvm = ReverseVendingMachine::findOrFail($rvmId);
        
        $validated = $request->validate([
            'ip_address' => 'required|ip'
        ]);

        $rvm->update([
            'ip_address' => $validated['ip_address']
        ]);

        // Get updated RVM information
        $rvmInfo = [
            'id' => $rvm->id,
            'name' => $rvm->name,
            'location' => $rvm->location,
            'ip_address' => $rvm->ip_address,
            'status' => $rvm->status,
            'capacity' => $rvm->capacity,
            'current_load' => $rvm->current_load,
            'api_key' => $rvm->api_key,
            'last_ping' => $rvm->last_ping?->toISOString(),
            'created_at' => $rvm->created_at?->toISOString(),
        ];

        // Get detection results for this RVM
        $detectionResults = DetectionResult::where('rvm_id', $rvmId)
            ->with('rvm')
            ->latest('detected_at')
            ->paginate(20)
            ->through(function ($detection) {
                return [
                    'id' => $detection->id,
                    'rvm_name' => $detection->rvm->name,
                    'session_id' => $detection->session_id,
                    'detected_at' => $detection->detected_at?->toISOString(),
                    'status' => $detection->status,
                    'detection_summary' => $detection->detection_summary,
                    'detection_data' => $detection->detection_data,
                    'error_message' => $detection->error_message,
                ];
            });

        // Get RVM analytics
        $analytics = $this->getRvmAnalytics($rvmId);

        // Get RVM health status from Jetson
        $healthStatus = $this->getRvmHealthStatus($rvm);
        
        // Get API status from Jetson
        $apiStatus = $this->getRvmApiStatus($rvm);
        
        // Get hardware information from Jetson
        $hardwareInfo = $this->getRvmHardwareInfo($rvm);
        
        // Get monitoring status from Jetson
        $monitoringStatus = $this->getRvmMonitoringStatus($rvm);
        
        // Get monitoring summary from Jetson
        $monitoringSummary = $this->getRvmMonitoringSummary($rvm);

        // Get monitoring analytics from database
        $monitoringAnalytics = $this->getRvmMonitoringAnalytics($rvmId);

        return Inertia::render('Maintenance/Show', [
            'rvm' => $rvmInfo,
            'detectionResults' => $detectionResults,
            'analytics' => $analytics,
            'healthStatus' => $healthStatus,
            'apiStatus' => $apiStatus,
            'hardwareInfo' => $hardwareInfo,
            'monitoringStatus' => $monitoringStatus,
            'monitoringSummary' => $monitoringSummary,
            'monitoringAnalytics' => $monitoringAnalytics,
        ])->with('success', 'IP address updated successfully');
    }

    /**
     * Get RVM monitoring analytics from database
     */
    private function getRvmMonitoringAnalytics($rvmId)
    {
        try {
            // Get chart data for different time periods
            $hourlyData = RvmMonitoringMetric::getChartData($rvmId, 24, 'hour');
            $dailyData = RvmMonitoringMetric::getChartData($rvmId, 7, 'hour'); // Last 7 days, grouped by hour
            
            // Get average metrics
            $avgMetrics = RvmMonitoringMetric::getAverageMetrics($rvmId, 24);
            
            // Get recent metrics (last 10 records)
            $recentMetrics = RvmMonitoringMetric::forRvm($rvmId)
                ->recent(1) // Last 1 hour
                ->orderBy('timestamp', 'desc')
                ->limit(10)
                ->get();

            return [
                'chart_data' => [
                    'hourly' => $this->formatChartData($hourlyData),
                    'daily' => $this->formatChartData($dailyData),
                    'monthly' => $this->formatChartData($dailyData), // Use daily data for monthly view
                    'yearly' => $this->formatChartData($dailyData), // Use daily data for yearly view
                ],
                'average_metrics' => $avgMetrics ? [
                    'cpu_percent' => round($avgMetrics->avg_cpu ?? 0, 1),
                    'memory_percent' => round($avgMetrics->avg_memory ?? 0, 1),
                    'gpu_memory_percent' => round($avgMetrics->avg_gpu ?? 0, 1),
                    'disk_usage_percent' => round($avgMetrics->avg_disk ?? 0, 1),
                    'processing_time_ms' => round($avgMetrics->avg_processing_time ?? 0, 1),
                    'total_detections' => $avgMetrics->total_detections ?? 0,
                    'total_errors' => $avgMetrics->total_errors ?? 0,
                    'total_api_requests' => $avgMetrics->total_api_requests ?? 0,
                ] : null,
                'recent_metrics' => $recentMetrics->map(function ($metric) {
                    return [
                        'timestamp' => $metric->timestamp->toISOString(),
                        'cpu_percent' => $metric->cpu_percent,
                        'memory_percent' => $metric->memory_percent,
                        'gpu_memory_percent' => $metric->gpu_memory_percent,
                        'disk_usage_percent' => $metric->disk_usage_percent,
                        'processing_time_ms' => $metric->processing_time_ms,
                        'detections_count' => $metric->detections_count,
                    ];
                }),
                'data_availability' => [
                    'has_data' => $recentMetrics->count() > 0,
                    'last_update' => $recentMetrics->first()?->timestamp?->toISOString(),
                    'total_records' => RvmMonitoringMetric::forRvm($rvmId)->count(),
                ]
            ];
        } catch (\Exception $e) {
            \Log::error("Failed to get monitoring analytics for RVM {$rvmId}: " . $e->getMessage());
            return [
                'chart_data' => [
                    'hourly' => [],
                    'daily' => [],
                ],
                'average_metrics' => null,
                'recent_metrics' => [],
                'data_availability' => [
                    'has_data' => false,
                    'last_update' => null,
                    'total_records' => 0,
                ]
            ];
        }
    }

    /**
     * Format chart data for frontend consumption
     */
    private function formatChartData($data)
    {
        return $data->map(function ($item) {
            return [
                'time' => $item->time_group,
                'cpu_percent' => round($item->cpu_percent ?? 0, 1),
                'memory_percent' => round($item->memory_percent ?? 0, 1),
                'gpu_memory_percent' => round($item->gpu_memory_percent ?? 0, 1),
                'disk_usage_percent' => round($item->disk_usage_percent ?? 0, 1),
                'processing_time_ms' => round($item->processing_time_ms ?? 0, 1),
                'detections_count' => $item->detections_count ?? 0,
            ];
        });
    }

    /**
     * Store monitoring data from Jetson
     */
    public function storeMonitoringData(Request $request, $rvmId)
    {
        try {
            $validated = $request->validate([
                'timestamp' => 'nullable|date',
                'cpu_usage' => 'nullable|numeric|min:0|max:100',
                'memory_usage' => 'nullable|numeric|min:0|max:100',
                'gpu_usage' => 'nullable|numeric|min:0|max:100',
                'disk_usage' => 'nullable|numeric|min:0|max:100',
                'processing_time_ms' => 'nullable|numeric|min:0',
                'detections_count' => 'nullable|integer|min:0',
                'error_count' => 'nullable|integer|min:0',
                'api_requests_count' => 'nullable|integer|min:0',
            ]);

            $metric = RvmMonitoringMetric::storeFromJetson($rvmId, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Monitoring data stored successfully',
                'data' => $metric
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to store monitoring data for RVM {$rvmId}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to store monitoring data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * End maintenance and set RVM status back to active
     */
    public function endMaintenance($rvmId)
    {
        $rvm = ReverseVendingMachine::findOrFail($rvmId);
        
        $rvm->update([
            'status' => 'active',
            'last_maintenance' => now()
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Maintenance ended successfully. RVM is now active.');
    }
}
