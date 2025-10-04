<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DetectionResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'rvm_id', 'session_id', 'user_id', 'detection_data',
        'image_path', 'detected_at', 'status', 'error_message', 'metadata'
    ];

    protected $casts = [
        'detection_data' => 'array',
        'metadata' => 'array',
        'detected_at' => 'datetime'
    ];

    // Relationships
    public function rvm(): BelongsTo
    {
        return $this->belongsTo(ReverseVendingMachine::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByRvm($query, $rvmId)
    {
        return $query->where('rvm_id', $rvmId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('detected_at', today());
    }

    // Accessors
    public function getDetectionSummaryAttribute()
    {
        if (!$this->detection_data) return 'No data';
        
        $count = count($this->detection_data['detections'] ?? []);
        return "{$count} objects detected";
    }

    // Static methods
    public static function getRvmStatistics($rvmId)
    {
        return [
            'total_detections' => self::byRvm($rvmId)->count(),
            'today_detections' => self::byRvm($rvmId)->today()->count(),
            'completed_detections' => self::byRvm($rvmId)->completed()->count(),
            'failed_detections' => self::byRvm($rvmId)->failed()->count(),
            'last_detection' => self::byRvm($rvmId)->latest('detected_at')->first()
        ];
    }

    public static function getGlobalStatistics()
    {
        return [
            'total_detections' => self::count(),
            'today_detections' => self::today()->count(),
            'completed_detections' => self::completed()->count(),
            'failed_detections' => self::failed()->count(),
            'pending_detections' => self::where('status', 'pending')->count(),
            'processing_detections' => self::where('status', 'processing')->count(),
            'last_detection' => self::latest('detected_at')->first(),
            'detections_by_status' => self::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray()
        ];
    }
}