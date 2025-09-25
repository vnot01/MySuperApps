<?php

namespace App\Http\Controllers;

use App\Models\ReverseVendingMachine;
use App\Models\RvmSession;
use App\Events\RvmStatusUpdated;
use App\Events\DashboardDataUpdated;
use App\Helpers\TimezoneHelper;
use App\Helpers\RvmStatusHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\DatabaseOptimizationService;

class AdminRvmController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['remoteRvmUI', 'dashboard']);
        $this->middleware('role:super-admin|admin|operator|technician')->except(['remoteRvmUI', 'dashboard']);
    }

    /**
     * Show RVM Dashboard with real data
     */
    public function dashboard()
    {
        // Get all RVMs with their calculated status using RvmStatusHelper
        $rvms = ReverseVendingMachine::all()->map(function ($rvm) {
            // Calculate status using RvmStatusHelper for consistency
            $calculatedStatus = RvmStatusHelper::calculateStatus($rvm->capacity, $rvm->special_status, $rvm->status);
            
            return [
                'id' => $rvm->id,
                'name' => $rvm->name,
                'location' => $rvm->location_description ?? 'Unknown Location',
                'capacity' => $rvm->capacity ?? 0,
                'special_status' => $rvm->special_status,
                'calculated_status' => $calculatedStatus,
                'status_info' => RvmStatusHelper::getStatusForJs($calculatedStatus),
                'last_seen' => $rvm->last_capacity_update ? 
                    TimezoneHelper::formatTime($rvm->last_capacity_update) : 
                    TimezoneHelper::formatTime($rvm->updated_at),
                'admin_access_pin' => $rvm->admin_access_pin,
                'remote_access_enabled' => $rvm->remote_access_enabled,
                'created_at' => $rvm->created_at,
                'updated_at' => $rvm->updated_at,
            ];
        });

        // Calculate statistics using consistent status calculation
        $statistics = [
            'total_rvm' => $rvms->count(),
            'active_sessions' => $rvms->where('calculated_status', 'active')->count(),
            'total_issues' => $rvms->where('calculated_status', 'full')->count(),
            'maintenance_count' => $rvms->where('calculated_status', 'maintenance')->count(),
            'inactive_count' => $rvms->where('calculated_status', 'inactive')->count(),
            'error_count' => $rvms->where('calculated_status', 'error')->count(),
        ];

        // Get timezone configuration
        $timezoneConfig = TimezoneHelper::getTimezoneInfo();

        return view('admin.rvm.dashboard', compact('rvms', 'statistics', 'timezoneConfig'));
    }

    /**
     * Get list of RVMs for admin dashboard with caching and optimization
     */
    public function getRvmList()
    {
        $rvms = ReverseVendingMachine::getCachedAdminList();

        return response()->json([
            'success' => true,
            'data' => $rvms,
            'cached' => true,
            'optimized' => true
        ]);
    }

    /**
     * Get RVM details for admin monitoring
     */
    public function getRvmDetails($rvmId)
    {
        $rvm = ReverseVendingMachine::with([
            'sessions' => function($query) {
                $query->where('status', 'active')
                      ->orWhere('created_at', '>=', now()->subHours(24))
                      ->orderBy('created_at', 'desc')
                      ->limit(10);
            }
        ])->find($rvmId);

        if (!$rvm) {
            return response()->json([
                'success' => false,
                'message' => 'RVM not found'
            ], 404);
        }

        // Get recent activity
        $recentSessions = RvmSession::where('rvm_id', $rvmId)
            ->where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'rvm' => $rvm,
                'recent_activity' => $recentSessions,
                'status_info' => $this->getRvmStatusInfo($rvm)
            ]
        ]);
    }

    /**
     * Remote access to RVM UI with security authentication
     */
    public function remoteAccess(Request $request, $rvmId)
    {
        $validator = Validator::make($request->all(), [
            'access_pin' => 'required|string|min:4|max:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get RVM details
        $rvm = ReverseVendingMachine::find($rvmId);
        if (!$rvm) {
            return response()->json([
                'success' => false,
                'message' => 'RVM not found'
            ], 404);
        }

        // Check if remote access is enabled for this RVM
        if (!$rvm->remote_access_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'Remote access is disabled for this RVM'
            ], 403);
        }

        $accessPin = $request->input('access_pin');
        
        // Verify access pin - check RVM specific pin first, then default pins
        $isValidPin = false;
        
        // Check RVM specific admin pin
        if ($rvm->admin_access_pin && $accessPin === $rvm->admin_access_pin) {
            $isValidPin = true;
        } else {
            // Fallback to default pins for testing
            $validPins = ['0000', '1234', '5678', '9999'];
            
            if (in_array($accessPin, $validPins)) {
                $isValidPin = true;
            }
        }

        if (!$isValidPin) {
            // Log failed access attempt
            \Log::warning("Failed RVM remote access attempt", [
                'rvm_id' => $rvmId,
                'attempted_pin' => $accessPin,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid access pin'
            ], 401);
        }

        // Log successful access
        \Log::info("Successful RVM remote access", [
            'rvm_id' => $rvmId,
            'ip_address' => $request->ip(),
            'timestamp' => now()
        ]);

        // Generate secure access token for remote session
        $accessToken = \Str::random(32);
        
        // Store access token in cache with expiration
        \Cache::put("admin_rvm_access:{$accessToken}", [
            'rvm_id' => $rvmId,
            'access_time' => now(),
            'expires_at' => now()->addHours(2),
            'user_role' => 'admin'
        ], now()->addHours(2));

        return response()->json([
            'success' => true,
            'data' => [
                'access_token' => $accessToken,
                'rvm' => $rvm,
                'access_url' => 'http://localhost:8000/admin/rvm/' . $rvmId . '/remote/' . $accessToken,
                'expires_at' => now()->addHours(2)->toISOString(),
                'kiosk_mode_enabled' => $rvm->kiosk_mode_enabled
            ]
        ]);
    }

    /**
     * Remote RVM UI view with security token
     */
    public function remoteRvmUI(Request $request, $rvmId, $token)
    {
        // Verify access token
        $accessData = \Cache::get("admin_rvm_access:{$token}");
        
        if (!$accessData || $accessData['rvm_id'] != $rvmId) {
            abort(403, 'Invalid or expired access token');
        }

        // Check if token is expired
        if (now()->isAfter($accessData['expires_at'])) {
            \Cache::forget("admin_rvm_access:{$token}");
            abort(403, 'Access token has expired');
        }

        // Get RVM details
        $rvm = ReverseVendingMachine::find($rvmId);
        if (!$rvm) {
            abort(404, 'RVM not found');
        }

        // Get WebSocket configuration
        $websocketUrl = config('reverb.apps.apps.0.options.host') . ':' . config('reverb.apps.apps.0.options.port');
        $websocketKey = config('reverb.apps.apps.0.key');
        $websocketSecret = config('reverb.apps.apps.0.secret');

        return view('admin.rvm.remote-ui', compact('rvm', 'rvmId', 'websocketUrl', 'websocketKey', 'websocketSecret', 'token'));
    }

    /**
     * Update RVM status remotely
     */
    public function updateRvmStatus(Request $request, $rvmId)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive,maintenance,full,error,unknown'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $rvm = ReverseVendingMachine::find($rvmId);
        if (!$rvm) {
            return response()->json([
                'success' => false,
                'message' => 'RVM not found'
            ], 404);
        }

        $oldStatus = $rvm->status;
        $rvm->update([
            'status' => $request->input('status'),
            'last_status_change' => now()
        ]);

        // Log status change
        \Log::info("RVM Status Changed", [
            'rvm_id' => $rvmId,
            'old_status' => $oldStatus,
            'new_status' => $request->input('status'),
            'changed_by' => 'admin',
            'changed_at' => now()
        ]);

        // Clear related caches
        $this->clearRvmCaches();
        
        // Broadcast status update event
        broadcast(new RvmStatusUpdated($rvm, $request->input('status')));

        return response()->json([
            'success' => true,
            'message' => 'RVM status updated successfully',
            'data' => [
                'rvm_id' => $rvmId,
                'old_status' => $oldStatus,
                'new_status' => $request->input('status'),
                'changed_at' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Update RVM POS settings
     */
    public function updateRvmSettings(Request $request, $rvmId)
    {
        $validator = Validator::make($request->all(), [
            'admin_access_pin' => 'nullable|string|min:4|max:8',
            'remote_access_enabled' => 'boolean',
            'kiosk_mode_enabled' => 'boolean',
            'pos_settings' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $rvm = ReverseVendingMachine::find($rvmId);
        if (!$rvm) {
            return response()->json([
                'success' => false,
                'message' => 'RVM not found'
            ], 404);
        }

        $rvm->update($request->only([
            'admin_access_pin',
            'remote_access_enabled',
            'kiosk_mode_enabled',
            'pos_settings'
        ]));

        // Log settings update
        \Log::info("RVM POS settings updated", [
            'rvm_id' => $rvmId,
            'updated_by' => 'admin',
            'settings' => $request->all(),
            'timestamp' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'RVM settings updated successfully',
            'data' => $rvm->fresh()
        ]);
    }

    /**
     * Get RVM status monitoring dashboard data with caching
     */
    public function getRvmMonitoring()
    {
        // Get RVMs with capacity and special status
        $rvms = ReverseVendingMachine::select([
            'id', 'name', 'location_description', 'status', 'capacity', 'special_status',
            'last_status_change', 'last_capacity_update', 'created_at', 'updated_at'
        ])->get();

        // Process RVM data with correct status logic using RvmStatusHelper
        $processedRvms = $rvms->map(function($rvm) {
            $statusData = RvmStatusHelper::getStatusData($rvm);
            
            return [
                'id' => $rvm->id,
                'name' => $rvm->name,
                'location' => $rvm->location_description,
                'capacity' => $rvm->capacity ?? 0,
                'status' => $statusData['status'],
                'special_status' => $rvm->special_status,
                'status_info' => $statusData,
                'last_seen' => $rvm->last_capacity_update ? 
                    TimezoneHelper::formatTime($rvm->last_capacity_update) : 
                    TimezoneHelper::formatTime($rvm->updated_at)
            ];
        });

        // Calculate statistics
        $statistics = [
            'total_rvm' => $rvms->count(),
            'active_sessions' => $processedRvms->where('status', 'active')->count(),
            'deposits_today' => $processedRvms->sum('capacity'),
            'total_issues' => $processedRvms->whereIn('status', ['error', 'full'])->count()
        ];

        $monitoringData = [
            'statistics' => $statistics,
            'rvms' => $processedRvms->toArray()
        ];

        return response()->json([
            'success' => true,
            'data' => $monitoringData,
            'cached' => false
        ]);
    }

    /**
     * Clear RVM related caches
     */
    private function clearRvmCaches()
    {
        // Clear RVM model cache
        ReverseVendingMachine::clearAllModelCache();
        
        // Clear specific cache prefixes
        $patterns = [
            'rvm:*',
            'admin_rvm_list_*',
            'admin_rvm_monitoring_*',
            'admin_rvm_details_*'
        ];
        
        foreach ($patterns as $pattern) {
            $keys = Cache::getRedis()->keys($pattern);
            if (!empty($keys)) {
                Cache::getRedis()->del($keys);
            }
        }
        
        \Log::info('RVM caches cleared', ['patterns' => $patterns]);
    }

    /**
     * Get RVM status information
     */
    private function getRvmStatusInfo($rvm)
    {
        $statusInfo = [
            'active' => [
                'label' => 'Active',
                'color' => 'green',
                'description' => 'RVM is running normally',
                'icon' => 'check-circle'
            ],
            'inactive' => [
                'label' => 'Inactive',
                'color' => 'gray',
                'description' => 'RVM is turned off',
                'icon' => 'pause-circle'
            ],
            'maintenance' => [
                'label' => 'Maintenance',
                'color' => 'yellow',
                'description' => 'RVM is under maintenance',
                'icon' => 'wrench'
            ],
            'full' => [
                'label' => 'Full',
                'color' => 'red',
                'description' => 'RVM storage is full',
                'icon' => 'exclamation-triangle'
            ],
            'error' => [
                'label' => 'Error',
                'color' => 'red',
                'description' => 'RVM has encountered an error',
                'icon' => 'x-circle'
            ],
            'unknown' => [
                'label' => 'Unknown',
                'color' => 'gray',
                'description' => 'RVM status is unknown',
                'icon' => 'question-mark-circle'
            ]
        ];

        return $statusInfo[$rvm->status] ?? $statusInfo['unknown'];
    }
}
