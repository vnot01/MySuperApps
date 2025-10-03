<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReverseVendingMachine;
use App\Models\TimezoneSyncLog;
use App\Models\DeviceTimezone;
use App\Models\Notification;
use App\Helpers\RvmStatusHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use App\Models\ReverseVendingMachine as Rvm;
use App\Jobs\CheckRvmPulse;
use App\Jobs\CheckRvmHealth;

class RvmController extends Controller
{
    /**
     * Display all RVMs with timezone information
     */
    public function index()
    {
        $rvms = ReverseVendingMachine::with(['timezoneSyncLogs' => function($query) {
            $query->latest()->limit(1);
        }])->get();

        // Process RVM data with correct status logic using RvmStatusHelper
        $rvms = $rvms->map(function($rvm) {
            $rvm->status_data = RvmStatusHelper::getStatusData($rvm);
            return $rvm;
        });

        // Calculate statistics - consistent with Dashboard using RvmStatusHelper
        $activeStatuses = RvmStatusHelper::getStatisticsConfig('active_statuses');
        $activeCount = ReverseVendingMachine::whereIn('status', $activeStatuses)->count();
        
        $timezoneSyncedCount = ReverseVendingMachine::whereNotNull('last_timezone_sync')
            ->where('last_timezone_sync', '>=', now()->subHours(24))
            ->count();

        $attentionStatuses = RvmStatusHelper::getStatisticsConfig('attention_statuses');
        $needsAttentionCount = ReverseVendingMachine::whereIn('status', $attentionStatuses)
            ->orWhereNull('timezone')
            ->orWhereNull('ip_address')
            ->orWhere('ip_address', '0.0.0.0')
            ->count();

        // Prepare statistics data
        $statistics = [
            'total' => $rvms->count(),
            'active' => $activeCount,
            'timezone_synced' => $timezoneSyncedCount,
            'needs_attention' => $needsAttentionCount
        ];

        // Check if this is an AJAX request
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'rvms' => $rvms,
                'statistics' => $statistics
            ]);
        }

        return view('admin.rvm.all-modern', compact('rvms', 'statistics', 'activeCount', 'timezoneSyncedCount', 'needsAttentionCount'));
    }

    /**
     * Display maintenance page
     */
    public function maintenance()
    {
        $rvms = ReverseVendingMachine::with(['timezoneSyncLogs' => function($query) {
            $query->latest()->limit(1);
        }])->get();

        // Process RVM data with correct status logic using RvmStatusHelper
        $rvms = $rvms->map(function($rvm) {
            $rvm->status_data = RvmStatusHelper::getStatusData($rvm);
            return $rvm;
        });

        // Calculate maintenance statistics using RvmStatusHelper for consistency
        $attentionStatuses = RvmStatusHelper::getStatisticsConfig('attention_statuses');
        $needsAttentionCount = ReverseVendingMachine::whereIn('status', $attentionStatuses)
            ->orWhereNull('timezone')
            ->orWhereNull('ip_address')
            ->orWhere('ip_address', '0.0.0.0')
            ->count();

        $timezoneIssuesCount = ReverseVendingMachine::whereNull('timezone')
            ->orWhere(function($query) {
                $query->whereNotNull('last_timezone_sync')
                      ->where('last_timezone_sync', '<=', now()->subHours(24));
            })
            ->count();

        $connectionIssuesCount = ReverseVendingMachine::whereNull('ip_address')
            ->orWhere('ip_address', '0.0.0.0')
            ->count();

        return view('admin.rvm.maintenance', compact('rvms', 'needsAttentionCount', 'timezoneIssuesCount', 'connectionIssuesCount'));
    }

    /**
     * Store a newly created RVM
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'address' => 'nullable|string',
            'ip_address' => 'nullable|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'timezone' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,maintenance,error',
            'capacity' => 'nullable|integer|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $timezone = $request->timezone ?? 'Asia/Jakarta';
            $status = $request->status ?? 'inactive';

            $rvm = ReverseVendingMachine::create([
                'name' => $request->name,
                'location' => $request->location,
                'address' => $request->address,
                'ip_address' => $request->ip_address,
                'port' => $request->port ?? 5001,
                'timezone' => $timezone,
                'timezone_offset' => $this->getTimezoneOffset($timezone),
                'status' => $status,
                'capacity' => $request->capacity ?? 0,
                'api_key' => $this->generateApiKey(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->route('admin.rvm.index')->with('success', 'RVM berhasil dibuat.');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create RVM: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ping RVM to check connection
     */
    public function ping(Request $request, $id)
    {
        $rvm = ReverseVendingMachine::findOrFail($id);

        if (!$rvm->ip_address) {
            return response()->json([
                'success' => false,
                'message' => 'No IP address configured for this RVM'
            ], 400);
        }

        try {
            $healthCheckUrl = "http://{$rvm->ip_address}:5002/rvm-health";
            $response = Http::timeout(5)->get($healthCheckUrl);

            $connectionStatus = 'disconnected';
            $success = false;
            $message = 'Connection failed';
            $responseTime = null;

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['status']) && $data['status'] === 'ok') {
                    $connectionStatus = 'connected';
                    $success = true;
                    $message = 'RVM is connected and healthy.';
                } else {
                    $message = 'RVM is reachable but reporting an unhealthy status.';
                }
                $responseTime = $response->transferStats->getHandlerStat('total_time') ?? null;
            } else {
                // If the first attempt fails, try the /health endpoint as a fallback
                $fallbackUrl = "http://{$rvm->ip_address}:5002/health";
                $response = Http::timeout(5)->get($fallbackUrl);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['status']) && $data['status'] === 'healthy') {
                        $connectionStatus = 'connected';
                        $success = true;
                        $message = 'RVM is connected and healthy (fallback).';
                    } else {
                        $message = 'RVM is reachable but reporting an unhealthy status (fallback).';
                    }
                    $responseTime = $response->transferStats->getHandlerStat('total_time') ?? null;
                } else {
                    $message = 'RVM is unreachable. Status: ' . $response->status();
                }
            }

            $newStatus = RvmStatusHelper::calculateStatus($rvm->capacity, $rvm->special_status, $connectionStatus);

            $rvm->update([
                'last_ping' => now(),
                'connection_status' => $connectionStatus,
                'status' => $newStatus
            ]);

            return response()->json([
                'success' => $success,
                'message' => $message,
                'data' => [
                    'ip_address' => $rvm->ip_address,
                    'response_time' => $responseTime,
                    'last_ping' => $rvm->last_ping,
                    'ping_result' => [
                        'success' => $success,
                        'message' => $message,
                        'response_time' => $responseTime,
                    ],
                    'type' => 'http_health_check'
                ]
            ]);

        } catch (\Exception $e) {
            $newStatus = RvmStatusHelper::calculateStatus($rvm->capacity, $rvm->special_status, 'disconnected');

            $rvm->update([
                'last_ping' => now(),
                'connection_status' => 'disconnected',
                'status' => $newStatus
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Ping failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync timezone for specific RVM
     */
    public function syncTimezone(Request $request, $id)
    {
        $rvm = ReverseVendingMachine::findOrFail($id);

        if (!$rvm->ip_address) {
            return response()->json([
                'success' => false,
                'message' => 'No IP address configured for this RVM'
            ], 400);
        }

        try {
            // Send timezone sync request to RVM
            $syncResult = $this->sendTimezoneSyncRequest($rvm);

            if ($syncResult['success']) {
                // Update RVM timezone info
                $rvm->update([
                    'timezone' => $syncResult['timezone'],
                    'timezone_offset' => $syncResult['timezone_offset'],
                    'last_timezone_sync' => now()
                ]);

                // Log timezone sync
                TimezoneSyncLog::create([
                    'device_id' => $rvm->id,
                    'device_type' => 'rvm',
                    'sync_type' => 'manual',
                    'old_timezone' => $rvm->getOriginal('timezone'),
                    'new_timezone' => $syncResult['timezone'],
                    'sync_timestamp' => now(),
                    'status' => 'success',
                    'details' => json_encode($syncResult)
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Timezone sync completed successfully',
                    'data' => [
                        'timezone' => $syncResult['timezone'],
                        'timezone_offset' => $syncResult['timezone_offset'],
                        'last_sync' => $rvm->last_timezone_sync
                    ]
                ]);
            } else {
                // Log failed sync
                TimezoneSyncLog::create([
                    'device_id' => $rvm->id,
                    'device_type' => 'rvm',
                    'sync_type' => 'manual',
                    'sync_timestamp' => now(),
                    'status' => 'failed',
                    'details' => json_encode($syncResult)
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Timezone sync failed: ' . $syncResult['message']
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Timezone sync error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set global timezone for all RVMs
     */
    public function setGlobalTimezone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'timezone' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $rvms = ReverseVendingMachine::whereNotNull('ip_address')->get();
            $successCount = 0;
            $failCount = 0;

            foreach ($rvms as $rvm) {
                try {
                    $syncResult = $this->sendTimezoneSyncRequest($rvm, $request->timezone);
                    
                    if ($syncResult['success']) {
                        $rvm->update([
                            'timezone' => $syncResult['timezone'],
                            'timezone_offset' => $syncResult['timezone_offset'],
                            'last_timezone_sync' => now()
                        ]);

                        TimezoneSyncLog::create([
                            'device_id' => $rvm->id,
                            'device_type' => 'rvm',
                            'sync_type' => 'bulk',
                            'old_timezone' => $rvm->getOriginal('timezone'),
                            'new_timezone' => $syncResult['timezone'],
                            'sync_timestamp' => now(),
                            'status' => 'success',
                            'details' => json_encode($syncResult)
                        ]);

                        $successCount++;
                    } else {
                        $failCount++;
                    }
                } catch (\Exception $e) {
                    $failCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Global timezone update completed. Success: {$successCount}, Failed: {$failCount}",
                'data' => [
                    'success_count' => $successCount,
                    'fail_count' => $failCount,
                    'total_rvms' => $rvms->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Global timezone update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get RVM details
     */
    public function show(Request $request, $id)
    {
        $rvm = ReverseVendingMachine::with(['timezoneSyncLogs' => function($query) {
            $query->latest()->limit(10);
        }])->findOrFail($id);

        // Get status data using the helper
        $statusData = RvmStatusHelper::getStatusData($rvm);

        // Calculate items needing attention for this specific RVM using the helper
        $statusData['needs_attention'] = RvmStatusHelper::getAttentionItemsCount($rvm);
        $rvm->status_data = $statusData;

        // Get recent metrics for the RVM
        $recentMetrics = \App\Models\ApplicationMetric::where('rvm_id', $id)
            ->latest()
            ->limit(10)
            ->get();

        // Get system metrics for the chart
        $systemMetrics = \Illuminate\Support\Facades\DB::table('system_metrics')
            ->where('rvm_id', $id)
            ->latest('created_at')
            ->limit(20)
            ->get(['cpu_usage', 'memory_usage', 'disk_usage', 'uptime', 'created_at']);

        // Get recent commands for the RVM
        $recentCommands = \App\Models\RemoteCommand::where('rvm_id', $id)
            ->latest()
            ->limit(10)
            ->get();

        // Get last transactions with user information
        $lastTransactions = \Illuminate\Support\Facades\DB::table('transactions')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->select(
                'transactions.id',
                'transactions.user_id',
                'transactions.type',
                'transactions.amount',
                'transactions.description',
                'transactions.created_at',
                'users.name as user_name',
                'users.email as user_email',
                'users.avatar as user_avatar'
            )
            ->latest('transactions.created_at')
            ->limit(5)
            ->get();

        // Check if this is an AJAX request
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'rvm' => $rvm,
                'recentMetrics' => $recentMetrics,
                'recentCommands' => $recentCommands,
                'systemMetrics' => $systemMetrics,
                'lastTransactions' => $lastTransactions
            ]);
        }

        return view('admin.rvm.show', compact('rvm', 'recentMetrics', 'recentCommands', 'systemMetrics', 'lastTransactions'));
    }

    /**
     * Update RVM
     */
    public function update(Request $request, $id)
    {
        $rvm = ReverseVendingMachine::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'location' => 'sometimes|string|max:255',
            'address' => 'nullable|string',
            'ip_address' => 'sometimes|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'timezone' => 'sometimes|string',
            'status' => 'sometimes|in:active,inactive,maintenance,error',
            'capacity' => 'nullable|integer|min:0|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = $request->only(['name', 'location', 'address', 'ip_address', 'port', 'timezone', 'status', 'capacity', 'latitude', 'longitude']);
            
            if (isset($updateData['timezone'])) {
                $updateData['timezone_offset'] = $this->getTimezoneOffset($updateData['timezone']);
            }

            $rvm->update($updateData);

            return redirect()->route('admin.rvm.index')->with('success', 'RVM berhasil diperbarui.');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update RVM: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete RVM
     */
    public function destroy($id)
    {
        $rvm = ReverseVendingMachine::findOrFail($id);

        try {
            $rvm->delete();

            return redirect()->route('admin.rvm.index')->with('success', 'RVM berhasil dihapus.');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete RVM: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Perform actual ping to RVM
     */
    /**
     * Perform network health check (IP ping only, no port testing)
     */
    private function performNetworkHealthCheck($ip)
    {
        $startTime = microtime(true);
        
        // Handle dummy data (0.0.0.0)
        if ($ip === '0.0.0.0' || $ip === 'localhost' || $ip === '127.0.0.1') {
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            return [
                'success' => true,
                'message' => 'Local/Dummy IP - No actual connection test',
                'response_time' => $responseTime,
                'is_dummy' => true,
                'is_local' => true,
                'connection_status' => 'local',
                'type' => 'network_health'
            ];
        }
        
        // Perform actual ICMP ping (network health check)
        try {
            // Use system ping command for network health check
            $pingCommand = "ping -c 1 -W 3 " . escapeshellarg($ip) . " 2>&1";
            $output = [];
            $returnCode = 0;
            
            exec($pingCommand, $output, $returnCode);
            $outputString = implode("\n", $output);
            
            // Check if ping was successful (return code 0 and contains success indicators)
            $successIndicators = [
                '1 received',
                '1 packets transmitted, 1 received',
                '1 packets transmitted, 1 packets received',
                '0% packet loss',
                'round-trip min/avg/max'
            ];
            
            $isSuccess = $returnCode === 0 && (
                strpos($outputString, '1 received') !== false ||
                strpos($outputString, '1 packets transmitted, 1 received') !== false ||
                strpos($outputString, '1 packets transmitted, 1 packets received') !== false ||
                strpos($outputString, '0% packet loss') !== false ||
                strpos($outputString, 'round-trip min/avg/max') !== false
            );
            
            if ($isSuccess) {
                // Extract response time from ping output (multiple formats)
                $responseTime = 0;
                if (preg_match('/time=([0-9.]+)/', $outputString, $matches)) {
                    $responseTime = floatval($matches[1]);
                } elseif (preg_match('/round-trip min\/avg\/max = ([0-9.]+)/', $outputString, $matches)) {
                    $responseTime = floatval($matches[1]);
                } elseif (preg_match('/([0-9.]+) ms/', $outputString, $matches)) {
                    $responseTime = floatval($matches[1]);
                }
                
                return [
                    'success' => true,
                    'message' => "Network health check successful - IP reachable",
                    'response_time' => $responseTime,
                    'type' => 'network_health',
                    'is_dummy' => false,
                    'debug_output' => $outputString
                ];
            } else {
                $responseTime = round((microtime(true) - $startTime) * 1000, 2);
                return [
                    'success' => false,
                    'message' => "Network health check failed - IP not reachable (return code: $returnCode)",
                    'response_time' => $responseTime,
                    'type' => 'network_health',
                    'is_dummy' => false,
                    'debug_output' => $outputString
                ];
            }
        } catch (\Exception $e) {
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            return [
                'success' => false,
                'message' => "Network health check error: " . $e->getMessage(),
                'response_time' => $responseTime,
                'type' => 'network_health',
                'is_dummy' => false
            ];
        }
    }

    /**
     * Perform service port testing (for remote access)
     */
    private function performPing($ip, $port = 8000)
    {
        $startTime = microtime(true);
        
        // Handle dummy data (0.0.0.0)
        if ($ip === '0.0.0.0' || $ip === 'localhost' || $ip === '127.0.0.1') {
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            return [
                'success' => true,
                'message' => 'Local/Dummy IP - No actual connection test',
                'response_time' => $responseTime,
                'is_dummy' => true,
                'is_local' => true,
                'connection_status' => 'local',
                'type' => 'service_port'
            ];
        }
        
        // Test multiple ports
        $ports = [8000, 5000, 5001]; // RVM API, Camera, Remote Access Controller
        $results = [];
        
        foreach ($ports as $testPort) {
            $portStartTime = microtime(true);
            
            try {
                $connection = @fsockopen($ip, $testPort, $errno, $errstr, 5);
                
                if ($connection) {
                    $responseTime = round((microtime(true) - $portStartTime) * 1000, 2);
                    fclose($connection);
                    
                    $results[$testPort] = [
                        'success' => true,
                        'message' => "Port $testPort: Connection successful",
                        'response_time' => $responseTime,
                        'service' => $this->getServiceName($testPort)
                    ];
                } else {
                    $responseTime = round((microtime(true) - $portStartTime) * 1000, 2);
                    $results[$testPort] = [
                        'success' => false,
                        'message' => "Port $testPort: Connection failed: $errstr ($errno)",
                        'response_time' => $responseTime,
                        'service' => $this->getServiceName($testPort)
                    ];
                }
            } catch (\Exception $e) {
                $responseTime = round((microtime(true) - $portStartTime) * 1000, 2);
                $results[$testPort] = [
                    'success' => false,
                    'message' => "Port $testPort: Connection error: " . $e->getMessage(),
                    'response_time' => $responseTime,
                    'service' => $this->getServiceName($testPort)
                ];
            }
        }
        
        // Determine overall success
        $overallSuccess = !empty(array_filter($results, function($result) {
            return $result['success'];
        }));
        
        return [
            'success' => $overallSuccess,
            'message' => 'Multi-port connectivity test',
            'response_time' => round((microtime(true) - $startTime) * 1000, 2),
            'is_dummy' => false,
            'ports' => $results
        ];
    }

    private function getServiceName($port)
    {
        $services = [
            8000 => 'RVM API',
            5000 => 'Camera Service',
            5001 => 'Remote Access Controller'
        ];
        
        return $services[$port] ?? "Port $port";
    }

    /**
     * Send timezone sync request to RVM
     */
    private function sendTimezoneSyncRequest($rvm, $timezone = null)
    {
        // This is a simulation - replace with actual API call to Jetson Orin
        // The Jetson Orin should have an endpoint to receive timezone sync requests
        
        $targetTimezone = $timezone ?? $rvm->timezone ?? 'Asia/Jakarta';
        
        // Simulate API call to Jetson Orin
        // $response = Http::timeout(10)->post("http://{$rvm->ip_address}:{$rvm->port}/api/timezone/sync", [
        //     'timezone' => $targetTimezone,
        //     'timestamp' => now()->toISOString()
        // ]);
        
        // For now, simulate success
        $success = rand(1, 10) <= 8; // 80% success rate
        
        if ($success) {
            return [
                'success' => true,
                'timezone' => $targetTimezone,
                'timezone_offset' => $this->getTimezoneOffset($targetTimezone),
                'message' => 'Timezone sync successful'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'RVM not responding or sync failed'
            ];
        }
    }

    /**
     * Get timezone offset
     */
    private function getTimezoneOffset($timezone)
    {
        try {
            $date = new \DateTime('now', new \DateTimeZone($timezone));
            return $date->format('P'); // Returns +07:00 format
        } catch (\Exception $e) {
            return '+00:00';
        }
    }

    /**
     * Test connection to RVM
     */
    public function testConnection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ip_address' => 'required|ip',
            'port' => 'nullable|integer|min:1|max:65535'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $ipAddress = $request->ip_address;
        $port = $request->port ?? 8001;

        // Use network health check for connection testing
        $pingResult = $this->performNetworkHealthCheck($ipAddress);

        return response()->json([
            'success' => $pingResult['success'],
            'message' => $pingResult['message'],
            'response_time' => $pingResult['response_time'],
            'is_dummy' => $pingResult['is_dummy'] ?? false,
            'type' => 'network_health'
        ]);
    }

    /**
     * Test service ports (for remote access)
     */
    public function testServicePorts(Request $request, $id)
    {
        $rvm = ReverseVendingMachine::findOrFail($id);

        if (!$rvm->ip_address) {
            return response()->json([
                'success' => false,
                'message' => 'No IP address configured for this RVM'
            ], 400);
        }

        try {
            $pingResult = $this->performPing($rvm->ip_address, $rvm->port ?? 8001);

            return response()->json([
                'success' => $pingResult['success'],
                'message' => $pingResult['message'],
                'data' => [
                    'ip_address' => $rvm->ip_address,
                    'port' => $rvm->port ?? 8001,
                    'response_time' => $pingResult['response_time'] ?? null,
                    'ping_result' => $pingResult,
                    'type' => 'service_port'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Service port test error: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Generate API key for RVM
     */
    private function generateApiKey()
    {
        return 'rvm_' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 16));
    }

    /**
     * Test RVM connection by ID
     */
    public function testRvmConnection($id)
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            
            // Test connection to RVM-Jetson
            $rvmApiUrl = "http://{$rvm->ip_address}:5000/health";
            
            $response = Http::timeout(5)->get($rvmApiUrl);
            
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'connected' => true,
                    'message' => 'RVM-Jetson is reachable and responding',
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'ip_address' => $rvm->ip_address,
                    'response_time' => $response->transferStats->getHandlerStat('total_time') ?? 'N/A'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'connected' => false,
                    'message' => 'RVM-Jetson responded with status: ' . $response->status(),
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'ip_address' => $rvm->ip_address
                ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'connected' => false,
                'message' => 'RVM-Jetson connection failed: ' . $e->getMessage(),
                'rvm_id' => $rvm->id ?? $id,
                'rvm_name' => $rvm->name ?? 'Unknown',
                'ip_address' => $rvm->ip_address ?? 'Unknown'
            ]);
        }
    }

    /**
     * Show Maintenance Mode page
     */
    public function maintenanceMode(Request $request, $id)
    {
        $rvm = ReverseVendingMachine::find($id);

        if (!$rvm) {
            abort(404, 'RVM not found');
        }

        // Get latest metrics
        $latestSystemMetrics = $rvm->systemMetrics()->latest('timestamp')->first();
        $latestApplicationMetrics = $rvm->applicationMetrics()->latest('recorded_at')->first();
        $latestNetworkInformation = $rvm->networkInformation()->latest('recorded_at')->first();

        // Get recent commands
        $recentCommands = $rvm->remoteCommands()->latest('created_at')->limit(10)->get();

        // Get software update info
        $latestSoftwareUpdate = $rvm->softwareUpdates()->latest('created_at')->first();
        $activeAiModel = $rvm->aiModels()->where('is_active', true)->first();

        return view('admin.rvm.maintenance-mode', compact(
            'rvm',
            'latestSystemMetrics',
            'latestApplicationMetrics', 
            'latestNetworkInformation',
            'recentCommands',
            'latestSoftwareUpdate',
            'activeAiModel'
        ));
    }

    /**
     * Toggle maintenance mode for RVM
     */
    public function toggleMaintenanceMode(Request $request, $id)
    {
        $rvm = ReverseVendingMachine::findOrFail($id);

        try {
            // Toggle maintenance mode
            $isCurrentlyInMaintenance = $rvm->special_status === 'maintenance';
            
            if ($isCurrentlyInMaintenance) {
                // Exit maintenance mode - set special_status to null so calculated_status uses capacity
                $rvm->update([
                    'special_status' => null,
                    'last_status_change' => now()
                ]);
                $message = 'RVM berhasil keluar dari Maintenance Mode';
                
                // Create notification for exiting maintenance mode
                try {
                    Notification::createNotification([
                        'notification_id' => 'rvm_maintenance_exit_' . $rvm->id . '_' . microtime(true),
                        'title' => 'RVM Maintenance Mode Ended',
                        'message' => "RVM {$rvm->name} has exited maintenance mode and is now operational.",
                        'type' => 'success',
                        'category' => 'rvm_status',
                        'data' => [
                            'rvm_id' => $rvm->id,
                            'rvm_name' => $rvm->name,
                            'action' => 'maintenance_mode_disabled'
                        ],
                        'is_system_wide' => true
                    ]);
                } catch (\Exception $e) {
                    // Log error but don't fail the maintenance mode operation
                    \Log::error('Failed to create maintenance mode exit notification: ' . $e->getMessage());
                }
            } else {
                // Enter maintenance mode
                $rvm->update([
                    'special_status' => 'maintenance',
                    'last_status_change' => now()
                ]);
                $message = 'RVM berhasil masuk ke Maintenance Mode';
                
                // Create notification for maintenance mode
                try {
                    Notification::createNotification([
                        'notification_id' => 'rvm_maintenance_' . $rvm->id . '_' . microtime(true),
                        'title' => 'RVM Maintenance Mode',
                        'message' => "RVM {$rvm->name} has entered maintenance mode.",
                        'type' => 'warning',
                        'category' => 'rvm_status',
                        'data' => [
                            'rvm_id' => $rvm->id,
                            'rvm_name' => $rvm->name,
                            'action' => 'maintenance_mode_enabled'
                        ],
                        'is_system_wide' => true
                    ]);
                } catch (\Exception $e) {
                    // Log error but don't fail the maintenance mode operation
                    \Log::error('Failed to create maintenance mode notification: ' . $e->getMessage());
                }
            }

            return response()->json(['success' => true, 'message' => $message]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengubah status maintenance: ' . $e->getMessage()], 500);
        }
    }
    public function manualPulseCheck(Request $request, Rvm $rvm = null)
    {
        if ($rvm) {
            CheckRvmPulse::dispatch($rvm);
        } else {
            $rvms = ReverseVendingMachine::all();
            foreach ($rvms as $rvm) {
                CheckRvmPulse::dispatch($rvm);
            }
        }
        return response()->json(['message' => 'Pulse check initiated.']);
    }

    public function manualHealthCheck(Request $request, Rvm $rvm = null)
    {
        if ($rvm) {
            CheckRvmHealth::dispatch($rvm);
        } else {
            $rvms = ReverseVendingMachine::all();
            foreach ($rvms as $rvm) {
                CheckRvmHealth::dispatch($rvm);
            }
        }
        return response()->json(['message' => 'Health check initiated.']);
    }
}
