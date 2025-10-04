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
        'rvm_id',
        'session_id',
        'user_id',
        'detection_data',
        'image_path',
        'detected_at',
        'status',
        'error_message',
        'metadata'
    ];

    protected $casts = [
        'detection_data' => 'array',
        'metadata' => 'array',
        'detected_at' => 'datetime'
    ];

    /**
     * Get the RVM that owns the detection result.
     */
    public function rvm(): BelongsTo
    {
        return $this->belongsTo(ReverseVendingMachine::class, 'rvm_id');
    }

    /**
     * Get the user that owns the detection result.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope a query to only include completed detections.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include failed detections.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include pending detections.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include detections for a specific RVM.
     */
    public function scopeByRvm($query, $rvmId)
    {
        return $query->where('rvm_id', $rvmId);
    }

    /**
     * Scope a query to only include today's detections.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('detected_at', Carbon::today());
    }

    /**
     * Scope a query to only include detections within a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('detected_at', [$startDate, $endDate]);
    }

    /**
     * Get detection summary for display.
     */
    public function getDetectionSummaryAttribute(): string
    {
        $data = $this->detection_data;
        
        if (empty($data)) {
            return 'No detection data available';
        }

        $summary = [];
        
        if (isset($data['waste_type'])) {
            $summary[] = "Type: {$data['waste_type']}";
        }
        
        if (isset($data['confidence'])) {
            $summary[] = "Confidence: " . round($data['confidence'], 1) . "%";
        }
        
        if (isset($data['weight'])) {
            $summary[] = "Weight: {$data['weight']}kg";
        }
        
        if (isset($data['quantity'])) {
            $summary[] = "Quantity: {$data['quantity']}";
        }

        return implode(' | ', $summary);
    }

    /**
     * Get formatted detected at time.
     */
    public function getFormattedDetectedAtAttribute(): string
    {
        return $this->detected_at->format('M d, Y H:i:s');
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'completed' => 'green',
            'failed' => 'red',
            'pending' => 'yellow',
            'processing' => 'blue',
            default => 'gray'
        };
    }

    /**
     * Get RVM statistics for a specific RVM.
     */
    public static function getRvmStatistics($rvmId, $days = 30)
    {
        $startDate = Carbon::now()->subDays($days);
        
        return [
            'total_detections' => self::byRvm($rvmId)->count(),
            'today_detections' => self::byRvm($rvmId)->today()->count(),
            'completed_detections' => self::byRvm($rvmId)->completed()->count(),
            'failed_detections' => self::byRvm($rvmId)->failed()->count(),
            'pending_detections' => self::byRvm($rvmId)->pending()->count(),
            'recent_detections' => self::byRvm($rvmId)
                ->where('detected_at', '>=', $startDate)
                ->count(),
            'success_rate' => self::byRvm($rvmId)->count() > 0 
                ? round((self::byRvm($rvmId)->completed()->count() / self::byRvm($rvmId)->count()) * 100, 2)
                : 0,
            'last_detection' => self::byRvm($rvmId)
                ->orderBy('detected_at', 'desc')
                ->first()?->detected_at?->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Get global statistics.
     */
    public static function getGlobalStatistics($days = 30)
    {
        $startDate = Carbon::now()->subDays($days);
        
        return [
            'total_detections' => self::count(),
            'today_detections' => self::today()->count(),
            'completed_detections' => self::completed()->count(),
            'failed_detections' => self::failed()->count(),
            'pending_detections' => self::pending()->count(),
            'recent_detections' => self::where('detected_at', '>=', $startDate)->count(),
            'success_rate' => self::count() > 0 
                ? round((self::completed()->count() / self::count()) * 100, 2)
                : 0,
            'active_rvms' => self::distinct('rvm_id')->count(),
            'detections_by_rvm' => self::selectRaw('rvm_id, COUNT(*) as count')
                ->groupBy('rvm_id')
                ->with('rvm:id,name')
                ->get()
        ];
    }

    /**
     * Get waste type distribution.
     */
    public static function getWasteTypeDistribution($rvmId = null, $days = 30)
    {
        $query = self::query();
        
        if ($rvmId) {
            $query->byRvm($rvmId);
        }
        
        $startDate = Carbon::now()->subDays($days);
        $query->where('detected_at', '>=', $startDate);
        
        return $query->selectRaw("
            JSON_EXTRACT(detection_data, '$.waste_type') as waste_type,
            COUNT(*) as count
        ")
        ->whereNotNull('detection_data->waste_type')
        ->groupBy('waste_type')
        ->orderBy('count', 'desc')
        ->get();
    }
}
