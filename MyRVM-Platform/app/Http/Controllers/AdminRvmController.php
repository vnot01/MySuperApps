<?php

namespace App\Http\Controllers;

use App\Models\ReverseVendingMachine;
use App\Models\RvmSession;
use App\Events\RvmStatusUpdated;
use App\Events\DashboardDataUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminRvmController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['remoteRvmUI']);
        $this->middleware('role:super-admin|admin|operator|technician')->except(['remoteRvmUI']);
    }

    /**
     * Get list of RVMs for admin dashboard
     */
    public function getRvmList()
    {
        $rvms = ReverseVendingMachine::select('id', 'name', 'location_description', 'status', 'last_status_change', 'created_at')
            ->withCount(['sessions as active_sessions' => function($query) {
                $query->where('status', 'active');
            }])
            ->withCount(['sessions as total_sessions_today' => function($query) {
                $query->whereDate('created_at', today());
            }])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rvms
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
     * Get RVM status monitoring dashboard data
     */
    public function getRvmMonitoring()
    {
        $rvms = ReverseVendingMachine::withCount([
            'sessions as active_sessions' => function($query) {
                $query->where('status', 'active');
            },
            'sessions as total_sessions_today' => function($query) {
                $query->whereDate('created_at', today());
            },
            'deposits as deposits_today' => function($query) {
                $query->whereDate('created_at', today());
            }
        ])->get();

        $statusCounts = $rvms->groupBy('status')->map->count();
        
        $monitoringData = [
            'total_rvms' => $rvms->count(),
            'status_counts' => $statusCounts,
            'active_sessions' => $rvms->sum('active_sessions'),
            'total_sessions_today' => $rvms->sum('total_sessions_today'),
            'total_deposits_today' => $rvms->sum('deposits_today'),
            'rvms' => $rvms->map(function($rvm) {
                return [
                    'id' => $rvm->id,
                    'name' => $rvm->name,
                    'location' => $rvm->location_description,
                    'status' => $rvm->status,
                    'status_info' => $this->getRvmStatusInfo($rvm),
                    'created_at' => $rvm->created_at,
                    'last_status_change' => $rvm->last_status_change,
                    'active_sessions' => $rvm->active_sessions,
                    'total_sessions_today' => $rvm->total_sessions_today,
                    'deposits_today' => $rvm->deposits_today,
                    'remote_access_enabled' => $rvm->remote_access_enabled,
                    'kiosk_mode_enabled' => $rvm->kiosk_mode_enabled,
                    'api_key' => $rvm->api_key
                ];
            })
        ];

        return response()->json([
            'success' => true,
            'data' => $monitoringData
        ]);
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
