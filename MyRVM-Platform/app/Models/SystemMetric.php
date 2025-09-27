<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'rvm_id',
        'cpu_usage',
        'memory_usage',
        'disk_usage',
        'gpu_usage',
        'temperature',
        'free_memory',
        'total_memory',
        'free_disk',
        'total_disk',
        'uptime',
        'process_count',
        'additional_metrics',
        'timestamp'
    ];

    protected $casts = [
        'cpu_usage' => 'decimal:2',
        'memory_usage' => 'decimal:2',
        'disk_usage' => 'decimal:2',
        'gpu_usage' => 'decimal:2',
        'temperature' => 'decimal:2',
        'free_memory' => 'integer',
        'total_memory' => 'integer',
        'free_disk' => 'integer',
        'total_disk' => 'integer',
        'uptime' => 'integer',
        'process_count' => 'integer',
        'additional_metrics' => 'array',
        'timestamp' => 'datetime'
    ];

    public function rvm(): BelongsTo
    {
        return $this->belongsTo(ReverseVendingMachine::class, 'rvm_id');
    }

    public function getMemoryUsagePercentageAttribute(): float
    {
        if (!$this->total_memory || $this->total_memory == 0) {
            return 0;
        }
        return round((($this->total_memory - $this->free_memory) / $this->total_memory) * 100, 2);
    }

    public function getDiskUsagePercentageAttribute(): float
    {
        if (!$this->total_disk || $this->total_disk == 0) {
            return 0;
        }
        return round((($this->total_disk - $this->free_disk) / $this->total_disk) * 100, 2);
    }

    public function getFormattedUptimeAttribute(): string
    {
        if (!$this->uptime) {
            return 'Unknown';
        }

        $seconds = $this->uptime;
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($days > 0) {
            return "{$days}d {$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h {$minutes}m {$secs}s";
        } else {
            return "{$minutes}m {$secs}s";
        }
    }

    public function getFormattedMemoryAttribute(): string
    {
        if (!$this->total_memory) {
            return 'Unknown';
        }

        $total = $this->formatBytes($this->total_memory);
        $free = $this->formatBytes($this->free_memory);
        $used = $this->formatBytes($this->total_memory - $this->free_memory);

        return "{$used} / {$total} (Free: {$free})";
    }

    public function getFormattedDiskAttribute(): string
    {
        if (!$this->total_disk) {
            return 'Unknown';
        }

        $total = $this->formatBytes($this->total_disk);
        $free = $this->formatBytes($this->free_disk);
        $used = $this->formatBytes($this->total_disk - $this->free_disk);

        return "{$used} / {$total} (Free: {$free})";
    }

    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('timestamp', '>=', now()->subHours($hours));
    }

    public function scopeByRvm($query, $rvmId)
    {
        return $query->where('rvm_id', $rvmId);
    }

    public function scopeHighCpuUsage($query, $threshold = 80)
    {
        return $query->where('cpu_usage', '>', $threshold);
    }

    public function scopeHighMemoryUsage($query, $threshold = 80)
    {
        return $query->where('memory_usage', '>', $threshold);
    }

    public function scopeHighDiskUsage($query, $threshold = 80)
    {
        return $query->where('disk_usage', '>', $threshold);
    }

    public function scopeHighTemperature($query, $threshold = 70)
    {
        return $query->where('temperature', '>', $threshold);
    }
}
