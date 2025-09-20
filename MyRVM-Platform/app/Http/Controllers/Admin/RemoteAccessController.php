<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RemoteAccessSession;
use App\Models\ReverseVendingMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RemoteAccessController extends Controller
{
    public function start(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|exists:users,id',
            'ip_address' => 'nullable|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'access_type' => 'nullable|in:camera,gui,both',
            'session_duration' => 'nullable|integer|min:5|max:480',
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            
            // Check if there's already an active session
            $activeSession = RemoteAccessSession::where('rvm_id', $id)
                ->where('status', 'active')
                ->whereNull('end_time')
                ->first();

            if ($activeSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Remote access session already active for this RVM'
                ], 409);
            }

            // Test service ports before starting remote access
            $accessType = $request->access_type ?? 'gui';
            $testPort = ($accessType === 'camera') ? 5000 : 5001;
            
            // Test the specific port for remote access
            $portTest = $this->testServicePort($rvm->ip_address, $testPort);
            if (!$portTest['success']) {
                return response()->json([
                    'success' => false,
                    'message' => "Service port $testPort is not available: " . $portTest['message']
                ], 503);
            }

            DB::beginTransaction();

            // Create new remote access session
            $session = RemoteAccessSession::create([
                'rvm_id' => $id,
                'admin_id' => $request->admin_id,
                'start_time' => now(),
                'status' => 'active',
                'ip_address' => $request->ip_address,
                'port' => $request->port ?? 5001,
                'reason' => $request->reason ?? 'Remote access session started'
            ]);

            // Update RVM status to maintenance
            $rvm->update([
                'status' => 'maintenance',
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Remote access session started successfully',
                'data' => [
                    'session_id' => $session->id,
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'status' => 'maintenance',
                    'start_time' => $session->start_time,
                    'admin_id' => $session->admin_id,
                    'access_type' => $request->access_type ?? 'gui',
                    'session_duration' => $request->session_duration ?? 60,
                    'port' => $session->port,
                    'ip_address' => $session->ip_address,
                    'reason' => $session->reason
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to start remote access session: ' . $e->getMessage()
            ], 500);
        }
    }

    public function stop(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            
            // Find active session
            $session = RemoteAccessSession::where('rvm_id', $id)
                ->where('admin_id', $request->admin_id)
                ->where('status', 'active')
                ->whereNull('end_time')
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active remote access session found'
                ], 404);
            }

            DB::beginTransaction();

            // End the session
            $session->update([
                'end_time' => now(),
                'status' => 'completed',
                'reason' => $request->reason ?? 'Session completed'
            ]);

            // Update RVM status back to active
            $rvm->update([
                'status' => 'active',
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Remote access session ended successfully',
                'data' => [
                    'session_id' => $session->id,
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'status' => 'active',
                    'duration' => $session->duration,
                    'end_time' => $session->end_time
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to end remote access session: ' . $e->getMessage()
            ], 500);
        }
    }

    public function status($id)
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            
            // Get active session
            $activeSession = RemoteAccessSession::where('rvm_id', $id)
                ->where('status', 'active')
                ->whereNull('end_time')
                ->with('admin')
                ->first();

            // Get recent sessions (last 10)
            $recentSessions = RemoteAccessSession::where('rvm_id', $id)
                ->with('admin')
                ->orderBy('start_time', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'current_status' => $rvm->status,
                    'active_session' => $activeSession ? [
                        'session_id' => $activeSession->id,
                        'admin_id' => $activeSession->admin_id,
                        'admin_name' => $activeSession->admin->name ?? 'Unknown Admin',
                        'start_time' => $activeSession->start_time,
                        'duration' => $activeSession->duration,
                        'ip_address' => $activeSession->ip_address,
                        'port' => $activeSession->port,
                        'reason' => $activeSession->reason
                    ] : null,
                    'recent_sessions' => $recentSessions->map(function ($session) {
                        return [
                            'session_id' => $session->id,
                            'admin_name' => $session->admin->name ?? 'Unknown Admin',
                            'start_time' => $session->start_time,
                            'end_time' => $session->end_time,
                            'duration' => $session->duration,
                            'status' => $session->status,
                            'reason' => $session->reason,
                            'ip_address' => $session->ip_address,
                            'port' => $session->port
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get remote access status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function history($id)
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            
            $sessions = RemoteAccessSession::where('rvm_id', $id)
                ->with('admin')
                ->orderBy('start_time', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'sessions' => $sessions->map(function ($session) {
                        return [
                            'id' => $session->id,
                            'admin_name' => $session->admin->name ?? 'Unknown Admin',
                            'start_time' => $session->start_time,
                            'end_time' => $session->end_time,
                            'duration' => $session->duration,
                            'status' => $session->status,
                            'reason' => $session->reason,
                            'ip_address' => $session->ip_address,
                            'port' => $session->port
                        ];
                    }),
                    'pagination' => [
                        'current_page' => $sessions->currentPage(),
                        'last_page' => $sessions->lastPage(),
                        'per_page' => $sessions->perPage(),
                        'total' => $sessions->total()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get remote access history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test service port for remote access
     */
    private function testServicePort($ip, $port)
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
        
        try {
            $connection = @fsockopen($ip, $port, $errno, $errstr, 5);
            
            if ($connection) {
                $responseTime = round((microtime(true) - $startTime) * 1000, 2);
                fclose($connection);
                
                return [
                    'success' => true,
                    'message' => "Port $port: Service available",
                    'response_time' => $responseTime,
                    'service' => $this->getServiceName($port)
                ];
            } else {
                $responseTime = round((microtime(true) - $startTime) * 1000, 2);
                return [
                    'success' => false,
                    'message' => "Port $port: Service not available - $errstr ($errno)",
                    'response_time' => $responseTime,
                    'service' => $this->getServiceName($port)
                ];
            }
        } catch (\Exception $e) {
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            return [
                'success' => false,
                'message' => "Port $port: Connection error - " . $e->getMessage(),
                'response_time' => $responseTime,
                'service' => $this->getServiceName($port)
            ];
        }
    }

    /**
     * Check port availability for remote access
     */
    public function checkPort(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'port' => 'required|integer|min:1|max:65535',
            'access_type' => 'nullable|in:camera,gui,both'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            $port = $request->port;
            $accessType = $request->access_type ?? 'gui';

            if (!$rvm->ip_address) {
                return response()->json([
                    'success' => false,
                    'message' => 'No IP address configured for this RVM'
                ], 400);
            }

            // Test the specific port
            $portTest = $this->testServicePort($rvm->ip_address, $port);
            
            // Determine port status
            $status = 'closed';
            $statusText = 'Closed';
            $statusClass = 'danger';
            
            if ($portTest['success']) {
                $status = 'open';
                $statusText = 'Open';
                $statusClass = 'success';
            } else {
                // Check if it's a connection refused vs timeout
                if (strpos($portTest['message'], 'Connection refused') !== false) {
                    $status = 'reject';
                    $statusText = 'Reject';
                    $statusClass = 'warning';
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'ip_address' => $rvm->ip_address,
                    'port' => $port,
                    'access_type' => $accessType,
                    'service_name' => $this->getServiceName($port),
                    'status' => $status,
                    'status_text' => $statusText,
                    'status_class' => $statusClass,
                    'response_time' => $portTest['response_time'],
                    'message' => $portTest['message'],
                    'is_dummy' => $portTest['is_dummy'] ?? false
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Port check error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get service name by port
     */
    private function getServiceName($port)
    {
        $services = [
            5000 => 'Camera Service',
            5001 => 'Remote Access Controller',
            8000 => 'RVM API'
        ];
        
        return $services[$port] ?? "Port $port";
    }
}
