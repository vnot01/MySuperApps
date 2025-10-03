<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\Cacheable;

class ReverseVendingMachine extends Model
{
    use HasFactory, Cacheable;

    protected $fillable = [
        'name',
        'location_description',
        'location',
        'address',
        'latitude',
        'longitude',
        'status',
        'capacity',
        'special_status',
        'api_key',
        'api_token',
        'api_token_expires_at',
        'last_api_access',
        'last_status_change',
        'last_capacity_update',
        'admin_access_pin',
        'remote_access_enabled',
        'kiosk_mode_enabled',
        'pos_settings',
        'ip_address',
        'port',
        'timezone',
        'timezone_offset',
        'last_timezone_sync',
        'last_ping',
        'connection_status',
        // Jetson integration fields
        'jetson_ip',
        'jetson_port',
        'jetson_status',
        'last_jetson_ping',
        'jetson_health_data',
        'jetson_gpu_info',
        'jetson_hardware_info',
        // CV Server integration fields
        'cv_server_ip',
        'cv_server_port',
        'cv_server_status',
        'last_cv_server_ping',
        'cv_server_health_data',
    ];

    protected $casts = [
        'pos_settings' => 'array',
        'remote_access_enabled' => 'boolean',
        'kiosk_mode_enabled' => 'boolean',
        'last_status_change' => 'datetime',
        'last_capacity_update' => 'datetime',
        'last_timezone_sync' => 'datetime',
        'last_ping' => 'datetime',
        'api_token_expires_at' => 'datetime',
        'last_api_access' => 'datetime',
        'capacity' => 'integer',
        'port' => 'integer',
    ];

    /**
     * Cache configuration
     */
    protected int $cacheTtl = 300; // 5 minutes

    /**
     * Determine RVM status based on capacity and special status
     */
    public function getCalculatedStatusAttribute(): string
    {
        // If there's a special status (maintenance, inactive, error, unknown), use it
        if ($this->special_status && in_array($this->special_status, ['maintenance', 'inactive', 'error', 'unknown'])) {
            return $this->special_status;
        }
        
        // Determine status based on capacity
        if ($this->capacity >= 100) {
            return 'full';
        } elseif ($this->capacity >= 0) {
            return 'active';
        } else {
            return 'unknown';
        }
    }

    /**
     * Get status information with color, icon, and description
     */
    public function getStatusInfoAttribute(): array
    {
        $status = $this->calculated_status;
        
        $statusMap = [
            'active' => [
                'color' => 'success',
                'icon' => 'fas fa-check-circle',
                'label' => 'Active',
                'description' => 'RVM is operational and ready'
            ],
            'full' => [
                'color' => 'danger',
                'icon' => 'fas fa-exclamation-triangle',
                'label' => 'Full',
                'description' => 'RVM storage is full'
            ],
            'maintenance' => [
                'color' => 'warning',
                'icon' => 'fas fa-tools',
                'label' => 'Maintenance',
                'description' => 'RVM is under maintenance'
            ],
            'inactive' => [
                'color' => 'secondary',
                'icon' => 'fas fa-pause-circle',
                'label' => 'Inactive',
                'description' => 'RVM is offline or disabled'
            ],
            'error' => [
                'color' => 'danger',
                'icon' => 'fas fa-times-circle',
                'label' => 'Error',
                'description' => 'RVM has encountered an error'
            ],
            'unknown' => [
                'color' => 'info',
                'icon' => 'fas fa-question-circle',
                'label' => 'Unknown',
                'description' => 'Status cannot be determined'
            ]
        ];
        
        return $statusMap[$status] ?? $statusMap['unknown'];
    }

    /**
     * RVM ini memiliki banyak histori deposit.
     */
    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class, 'rvm_id');
    }

    /**
     * RVM ini memiliki banyak session.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(RvmSession::class, 'rvm_id');
    }

    /**
     * RVM ini memiliki banyak processing engines.
     */
    public function processingEngines(): BelongsToMany
    {
        return $this->belongsToMany(ProcessingEngine::class, 'rvm_processing_engines', 'rvm_id', 'processing_engine_id')
                    ->withPivot(['priority', 'is_active'])
                    ->withTimestamps();
    }

    /**
     * Get primary processing engine for this RVM
     */
    public function primaryProcessingEngine()
    {
        return $this->processingEngines()
                    ->wherePivot('priority', 'primary')
                    ->wherePivot('is_active', true)
                    ->first();
    }

    /**
     * Get Jetson processing engine for this RVM
     */
    public function jetsonProcessingEngine()
    {
        return $this->processingEngines()
                    ->where('type', 'jetson_edge')
                    ->wherePivot('is_active', true)
                    ->first();
    }

    /**
     * Get detection results for this RVM
     */
    public function detectionResults(): HasMany
    {
        return $this->hasMany(DetectionResult::class, 'rvm_id');
    }

    /**
     * Get cached RVM statistics
     */
    public function getCachedStats(): array
    {
        return $this->cacheModel('stats', function () {
            return [
                'deposits_count' => $this->deposits()->count(),
                'sessions_count' => $this->sessions()->count(),
                'active_sessions_count' => $this->sessions()->where('status', 'active')->count(),
                'completed_deposits' => $this->deposits()->where('status', 'completed')->count(),
                'pending_deposits' => $this->deposits()->where('status', 'pending')->count(),
                'total_rewards_given' => $this->deposits()->where('status', 'completed')->sum('reward_amount'),
            ];
        }, [], 300); // 5 minutes cache
    }

    /**
     * Get cached recent deposits
     */
    public function getCachedRecentDeposits(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return $this->cacheModel('recent_deposits', function () use ($limit) {
            return $this->deposits()
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        }, ['limit' => $limit], 180); // 3 minutes cache
    }

    /**
     * Get cached recent sessions
     */
    public function getCachedRecentSessions(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return $this->cacheModel('recent_sessions', function () use ($limit) {
            return $this->sessions()
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        }, ['limit' => $limit], 180); // 3 minutes cache
    }

    /**
     * Get cached RVM list for admin dashboard
     */
    public static function getCachedAdminList(): \Illuminate\Database\Eloquent\Collection
    {
        $model = new static();
        
        return $model->cacheModel('admin_list', function () {
            return static::select('id', 'name', 'location_description', 'status', 'last_status_change', 'created_at')
                ->withCount(['sessions as active_sessions' => function($query) {
                    $query->where('status', 'active');
                }])
                ->withCount(['sessions as total_sessions_today' => function($query) {
                    $query->whereDate('created_at', today());
                }])
                ->orderBy('name')
                ->get();
        }, [], 600); // 10 minutes cache
    }

    /**
     * Get timezone sync logs for this RVM
     */
    public function timezoneSyncLogs(): HasMany
    {
        return $this->hasMany(TimezoneSyncLog::class, 'device_id')->where('device_type', 'rvm');
    }

    public function remoteAccessSessions(): HasMany
    {
        return $this->hasMany(RemoteAccessSession::class, 'rvm_id');
    }

    public function configurations(): HasMany
    {
        return $this->hasMany(RvmConfiguration::class, 'rvm_id');
    }

    public function backupLogs(): HasMany
    {
        return $this->hasMany(BackupLog::class, 'rvm_id');
    }

    public function systemMetrics(): HasMany
    {
        return $this->hasMany(SystemMetric::class, 'rvm_id');
    }

    public function applicationMetrics(): HasMany
    {
        return $this->hasMany(ApplicationMetric::class, 'rvm_id');
    }

    public function networkInformation(): HasMany
    {
        return $this->hasMany(NetworkInformation::class, 'rvm_id');
    }

    public function remoteCommands(): HasMany
    {
        return $this->hasMany(RemoteCommand::class, 'rvm_id');
    }

    public function softwareUpdates(): HasMany
    {
        return $this->hasMany(SoftwareUpdate::class, 'rvm_id');
    }

    public function aiModels(): HasMany
    {
        return $this->hasMany(AiModel::class, 'rvm_id');
    }

    /**
     * Get cached RVM monitoring data
     */
    public static function getCachedMonitoringData(): array
    {
        $model = new static();
        
        return $model->cacheModel('monitoring_data', function () {
            $rvms = static::withCount([
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
            
            return [
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
        }, [], 60); // 1 minute cache
    }

    /**
     * Warm up RVM model cache
     */
    public function warmUpModelCache(): array
    {
        $results = [];
        
        try {
            // Cache RVM statistics
            $results['stats'] = $this->getCachedStats();
            
            // Cache recent deposits
            $results['recent_deposits'] = $this->getCachedRecentDeposits();
            
            // Cache recent sessions
            $results['recent_sessions'] = $this->getCachedRecentSessions();
            
        } catch (\Exception $e) {
            \Log::error("RVM cache warm up error", [
                'rvm_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }
}
