<?php
/**
 * DetectionResult Model
 * 
 * This model should be added to your MyRVM-Platform Laravel application
 * to handle detection results from MyCV-Platform.
 */

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
     * Get the RVM that owns this detection result
     */
    public function rvm(): BelongsTo
    {
        return $this->belongsTo(ReverseVendingMachine::class, 'rvm_id');
    }

    /**
     * Scope for completed detections
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed detections
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for detections by RVM
     */
    public function scopeForRvm($query, $rvmId)
    {
        return $query->where('rvm_id', $rvmId);
    }

    /**
     * Scope for detections by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('detected_at', [$startDate, $endDate]);
    }

    /**
     * Scope for today's detections
     */
    public function scopeToday($query)
    {
        return $query->whereDate('detected_at', today());
    }

    /**
     * Scope for this week's detections
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('detected_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope for this month's detections
     */
    public function scopeThisMonth($query)
    {
        return $query->whereBetween('detected_at', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    /**
     * Get detection count by class name
     */
    public function getDetectionCountByClass(): array
    {
        $detectionData = $this->detection_data ?? [];
        $classCounts = [];

        foreach ($detectionData as $detection) {
            $className = $detection['class_name'] ?? 'unknown';
            $classCounts[$className] = ($classCounts[$className] ?? 0) + 1;
        }

        return $classCounts;
    }

    /**
     * Get total detection count
     */
    public function getTotalDetectionsAttribute(): int
    {
        return count($this->detection_data ?? []);
    }

    /**
     * Get detection summary
     */
    public function getDetectionSummaryAttribute(): array
    {
        $detectionData = $this->detection_data ?? [];
        
        if (empty($detectionData)) {
            return [
                'total_detections' => 0,
                'classes_detected' => [],
                'confidence_scores' => []
            ];
        }

        $classes = [];
        $confidences = [];

        foreach ($detectionData as $detection) {
            $className = $detection['class_name'] ?? 'unknown';
            $confidence = $detection['confidence'] ?? 0;
            
            $classes[] = $className;
            $confidences[] = $confidence;
        }

        return [
            'total_detections' => count($detectionData),
            'classes_detected' => array_unique($classes),
            'confidence_scores' => [
                'min' => min($confidences),
                'max' => max($confidences),
                'avg' => array_sum($confidences) / count($confidences)
            ]
        ];
    }

    /**
     * Check if detection is recent (within last hour)
     */
    public function isRecent(): bool
    {
        return $this->detected_at->isAfter(now()->subHour());
    }

    /**
     * Get formatted detected at time
     */
    public function getFormattedDetectedAtAttribute(): string
    {
        return $this->detected_at->format('Y-m-d H:i:s');
    }

    /**
     * Get human readable time ago
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->detected_at->diffForHumans();
    }

    /**
     * Static method to get statistics for RVM
     */
    public static function getRvmStatistics(int $rvmId): array
    {
        $rvm = ReverseVendingMachine::find($rvmId);
        if (!$rvm) {
            return [];
        }

        $baseQuery = static::where('rvm_id', $rvmId);

        return [
            'total_detections' => $baseQuery->count(),
            'completed_detections' => $baseQuery->completed()->count(),
            'failed_detections' => $baseQuery->failed()->count(),
            'detections_today' => $baseQuery->today()->count(),
            'detections_this_week' => $baseQuery->thisWeek()->count(),
            'detections_this_month' => $baseQuery->thisMonth()->count(),
            'last_detection' => $baseQuery->latest('detected_at')->first()?->detected_at,
            'avg_detections_per_day' => static::getAverageDetectionsPerDay($rvmId),
            'most_detected_class' => static::getMostDetectedClass($rvmId)
        ];
    }

    /**
     * Get average detections per day for RVM
     */
    public static function getAverageDetectionsPerDay(int $rvmId): float
    {
        $firstDetection = static::where('rvm_id', $rvmId)
            ->orderBy('detected_at')
            ->first();

        if (!$firstDetection) {
            return 0;
        }

        $daysSinceFirst = $firstDetection->detected_at->diffInDays(now());
        if ($daysSinceFirst === 0) {
            return static::where('rvm_id', $rvmId)->count();
        }

        return static::where('rvm_id', $rvmId)->count() / $daysSinceFirst;
    }

    /**
     * Get most detected class for RVM
     */
    public static function getMostDetectedClass(int $rvmId): ?string
    {
        $detections = static::where('rvm_id', $rvmId)
            ->whereNotNull('detection_data')
            ->get();

        $classCounts = [];

        foreach ($detections as $detection) {
            $detectionData = $detection->detection_data ?? [];
            foreach ($detectionData as $item) {
                $className = $item['class_name'] ?? 'unknown';
                $classCounts[$className] = ($classCounts[$className] ?? 0) + 1;
            }
        }

        if (empty($classCounts)) {
            return null;
        }

        return array_keys($classCounts, max($classCounts))[0];
    }
}
