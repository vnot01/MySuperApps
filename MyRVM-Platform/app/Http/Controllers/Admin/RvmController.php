<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReverseVendingMachine;
use App\Models\TimezoneSyncLog;
use App\Models\DeviceTimezone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

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

        // Calculate statistics - consistent with Dashboard
        $activeCount = $rvms->where('status', 'active')->count();
        
        $timezoneSyncedCount = $rvms->filter(function($rvm) {
            return $rvm->last_timezone_sync && 
                   Carbon::parse($rvm->last_timezone_sync)->diffInHours(now()) < 24;
        })->count();

        $needsAttentionCount = $rvms->filter(function($rvm) {
            return $rvm->status === 'error' || 
                   $rvm->status === 'maintenance' ||
                   $rvm->status === 'inactive' ||
                   $rvm->status === 'full' ||
                   !$rvm->timezone ||
                   !$rvm->ip_address ||
                   $rvm->ip_address === '0.0.0.0';
        })->count();

        return view('admin.rvm.all', compact('rvms', 'activeCount', 'timezoneSyncedCount', 'needsAttentionCount'));
    }

    /**
     * Display maintenance page
     */
    public function maintenance()
    {
        $rvms = ReverseVendingMachine::with(['timezoneSyncLogs' => function($query) {
            $query->latest()->limit(1);
        }])->get();

        // Calculate maintenance statistics
        $timezoneIssuesCount = $rvms->filter(function($rvm) {
            return !$rvm->timezone || 
                   ($rvm->last_timezone_sync && 
                    Carbon::parse($rvm->last_timezone_sync)->diffInHours(now()) > 24);
        })->count();

        $connectionIssuesCount = $rvms->filter(function($rvm) {
            return !$rvm->ip_address;
        })->count();

        return view('admin.rvm.maintenance', compact('rvms', 'timezoneIssuesCount', 'connectionIssuesCount'));
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
            'ip_address' => 'required|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'timezone' => 'required|string',
            'status' => 'required|in:active,inactive,maintenance,error'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $rvm = ReverseVendingMachine::create([
                'name' => $request->name,
                'location' => $request->location,
                'address' => $request->address,
                'ip_address' => $request->ip_address,
                'port' => $request->port ?? 8000,
                'timezone' => $request->timezone,
                'timezone_offset' => $this->getTimezoneOffset($request->timezone),
                'status' => $request->status,
                'api_key' => $this->generateApiKey(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'RVM created successfully',
                'data' => $rvm
            ]);

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
            // Simulate ping (replace with actual ping implementation)
            $pingResult = $this->performPing($rvm->ip_address, $rvm->port ?? 8000);
            
            // Update last ping time
            $rvm->update([
                'last_ping' => now(),
                'connection_status' => $pingResult['success'] ? 'connected' : 'disconnected'
            ]);

            return response()->json([
                'success' => $pingResult['success'],
                'message' => $pingResult['message'],
                'data' => [
                    'ip_address' => $rvm->ip_address,
                    'port' => $rvm->port ?? 8000,
                    'response_time' => $pingResult['response_time'] ?? null,
                    'last_ping' => $rvm->last_ping
                ]
            ]);

        } catch (\Exception $e) {
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
    public function show($id)
    {
        $rvm = ReverseVendingMachine::with(['timezoneSyncLogs' => function($query) {
            $query->latest()->limit(10);
        }])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $rvm
        ]);
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
            'status' => 'sometimes|in:active,inactive,maintenance,error'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = $request->only(['name', 'location', 'address', 'ip_address', 'port', 'timezone', 'status']);
            
            if (isset($updateData['timezone'])) {
                $updateData['timezone_offset'] = $this->getTimezoneOffset($updateData['timezone']);
            }

            $rvm->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'RVM updated successfully',
                'data' => $rvm
            ]);

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

            return response()->json([
                'success' => true,
                'message' => 'RVM deleted successfully'
            ]);

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
    private function performPing($ip, $port = 8000)
    {
        $startTime = microtime(true);
        
        // Handle dummy data (0.0.0.0)
        if ($ip === '0.0.0.0' || $ip === 'localhost' || $ip === '127.0.0.1') {
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            return [
                'success' => true,
                'message' => 'Dummy data - No actual connection test',
                'response_time' => $responseTime,
                'is_dummy' => true
            ];
        }
        
        // Real ping implementation for actual IP addresses
        try {
            // Try to connect to the RVM
            $connection = @fsockopen($ip, $port, $errno, $errstr, 5);
            
            if ($connection) {
                $responseTime = round((microtime(true) - $startTime) * 1000, 2);
                fclose($connection);
                
                return [
                    'success' => true,
                    'message' => 'Connection successful',
                    'response_time' => $responseTime,
                    'is_dummy' => false
                ];
            } else {
                $responseTime = round((microtime(true) - $startTime) * 1000, 2);
                return [
                    'success' => false,
                    'message' => "Connection failed: $errstr ($errno)",
                    'response_time' => $responseTime,
                    'is_dummy' => false
                ];
            }
        } catch (\Exception $e) {
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
                'response_time' => $responseTime,
                'is_dummy' => false
            ];
        }
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
        $port = $request->port ?? 8000;

        $pingResult = $this->performPing($ipAddress, $port);

        return response()->json([
            'success' => $pingResult['success'],
            'message' => $pingResult['message'],
            'response_time' => $pingResult['response_time'],
            'is_dummy' => $pingResult['is_dummy'] ?? false
        ]);
    }

    /**
     * Generate API key for RVM
     */
    private function generateApiKey()
    {
        return 'rvm_' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 16));
    }
}
