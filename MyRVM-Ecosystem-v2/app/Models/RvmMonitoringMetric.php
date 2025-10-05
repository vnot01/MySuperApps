<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RvmMonitoringMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'rvm_id',
        'timestamp',
        'cpu_percent',
        'memory_percent',
        'gpu_memory_percent',
        'disk_usage_percent',
        'processing_time_ms',
        'detections_count',
        'error_count',
        'api_requests_count',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'cpu_percent' => 'decimal:2',
        'memory_percent' => 'decimal:2',
        'gpu_memory_percent' => 'decimal:2',
        'disk_usage_percent' => 'decimal:2',
        'processing_time_ms' => 'decimal:2',
        'detections_count' => 'integer',
        'error_count' => 'integer',
        'api_requests_count' => 'integer',
    ];

    /**
     * Get the RVM that owns the monitoring metric.
     */
    public function rvm(): BelongsTo
    {
        return $this->belongsTo(ReverseVendingMachine::class, 'rvm_id');
    }

    /**
     * Scope to get metrics for a specific RVM.
     */
    public function scopeForRvm($query, $rvmId)
    {
        return $query->where('rvm_id', $rvmId);
    }

    /**
     * Scope to get metrics within a time range.
     */
    public function scopeInTimeRange($query, $startTime, $endTime)
    {
        return $query->whereBetween('timestamp', [$startTime, $endTime]);
    }

    /**
     * Scope to get recent metrics.
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('timestamp', '>=', now()->subHours($hours));
    }

    /**
     * Get average metrics for a time period.
     */
    public static function getAverageMetrics($rvmId, $hours = 24)
    {
        return self::forRvm($rvmId)
            ->recent($hours)
            ->selectRaw('
                AVG(cpu_percent) as avg_cpu,
                AVG(memory_percent) as avg_memory,
                AVG(gpu_memory_percent) as avg_gpu,
                AVG(disk_usage_percent) as avg_disk,
                AVG(processing_time_ms) as avg_processing_time,
                SUM(detections_count) as total_detections,
                SUM(error_count) as total_errors,
                SUM(api_requests_count) as total_api_requests
            ')
            ->first();
    }

    /**
     * Get metrics for chart data.
     */
    public static function getChartData($rvmId, $hours = 24, $interval = 'hour')
    {
        $query = self::forRvm($rvmId)->recent($hours);

        switch ($interval) {
            case 'minute':
                $query->selectRaw('
                    DATE_TRUNC(\'minute\', timestamp) as time_group,
                    AVG(cpu_percent) as cpu_percent,
                    AVG(memory_percent) as memory_percent,
                    AVG(gpu_memory_percent) as gpu_memory_percent,
                    AVG(disk_usage_percent) as disk_usage_percent,
                    AVG(processing_time_ms) as processing_time_ms,
                    SUM(detections_count) as detections_count
                ')
                ->groupBy('time_group')
                ->orderBy('time_group');
                break;
            
            case 'hour':
            default:
                $query->selectRaw('
                    DATE_TRUNC(\'hour\', timestamp) as time_group,
                    AVG(cpu_percent) as cpu_percent,
                    AVG(memory_percent) as memory_percent,
                    AVG(gpu_memory_percent) as gpu_memory_percent,
                    AVG(disk_usage_percent) as disk_usage_percent,
                    AVG(processing_time_ms) as processing_time_ms,
                    SUM(detections_count) as detections_count
                ')
                ->groupBy('time_group')
                ->orderBy('time_group');
                break;
        }

        return $query->get();
    }

    /**
     * Store monitoring data from Jetson.
     */
    public static function storeFromJetson($rvmId, $data)
    {
        return self::create([
            'rvm_id' => $rvmId,
            'timestamp' => $data['timestamp'] ?? now(),
            'cpu_percent' => $data['cpu_usage'] ?? null,
            'memory_percent' => $data['memory_usage'] ?? null,
            'gpu_memory_percent' => $data['gpu_usage'] ?? null,
            'disk_usage_percent' => $data['disk_usage'] ?? null,
            'processing_time_ms' => $data['processing_time_ms'] ?? null,
            'detections_count' => $data['detections_count'] ?? 0,
            'error_count' => $data['error_count'] ?? 0,
            'api_requests_count' => $data['api_requests_count'] ?? 0,
        ]);
    }
}

