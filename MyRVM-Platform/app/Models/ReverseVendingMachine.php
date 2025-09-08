<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Cacheable;

class ReverseVendingMachine extends Model
{
    use HasFactory, Cacheable;

    protected $fillable = [
        'name',
        'location_description',
        'status',
        'api_key',
        'last_status_change',
        'admin_access_pin',
        'remote_access_enabled',
        'kiosk_mode_enabled',
        'pos_settings',
    ];

    protected $casts = [
        'pos_settings' => 'array',
        'remote_access_enabled' => 'boolean',
        'kiosk_mode_enabled' => 'boolean',
        'last_status_change' => 'datetime',
    ];

    /**
     * Cache configuration
     */
    protected int $cacheTtl = 300; // 5 minutes

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
