<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProcessingEngine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'server_address',
        'port',
        'gpu_memory_limit',
        'docker_gpu_passthrough',
        'model_path',
        'processing_timeout',
        'auto_failover',
        'is_active',
        'is_online',
        'last_ping_at',
        'ping_response_time',
        'health_status',
    ];

    protected $casts = [
        'docker_gpu_passthrough' => 'boolean',
        'auto_failover' => 'boolean',
        'is_active' => 'boolean',
        'is_online' => 'boolean',
        'last_ping_at' => 'datetime',
        'health_status' => 'array',
    ];

    /**
     * Get the RVMs that use this processing engine
     */
    public function rvms(): BelongsToMany
    {
        return $this->belongsToMany(ReverseVendingMachine::class, 'rvm_processing_engines', 'processing_engine_id', 'rvm_id')
                    ->withPivot(['priority', 'is_active'])
                    ->withTimestamps();
    }

    /**
     * Scope for NVIDIA CUDA engines
     */
    public function scopeNvidiaCuda($query)
    {
        return $query->where('type', 'nvidia_cuda');
    }

    /**
     * Scope for Jetson Edge engines
     */
    public function scopeJetsonEdge($query)
    {
        return $query->where('type', 'jetson_edge');
    }

    /**
     * Scope for active engines
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for online engines
     */
    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }

    /**
     * Get health status color
     */
    public function getHealthStatusColorAttribute(): string
    {
        if (!$this->is_online) {
            return 'danger';
        }

        if ($this->ping_response_time > 1000) {
            return 'warning';
        }

        return 'success';
    }

    /**
     * Get formatted ping response time
     */
    public function getFormattedPingTimeAttribute(): string
    {
        if (!$this->ping_response_time) {
            return 'N/A';
        }

        return $this->ping_response_time . 'ms';
    }
}