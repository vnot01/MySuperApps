<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetectionResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'rvm_id',
        'image_path',
        'detections',
        'timestamp',
        'processing_time',
        'model_version',
        'status',
        'processing_type',
        'priority'
    ];

    protected $casts = [
        'detections' => 'array',
        'timestamp' => 'datetime',
        'processing_time' => 'float',
    ];

    /**
     * Get the RVM that owns the detection result
     */
    public function reverseVendingMachine(): BelongsTo
    {
        return $this->belongsTo(ReverseVendingMachine::class, 'rvm_id');
    }

    /**
     * Get formatted detections
     */
    public function getFormattedDetectionsAttribute(): array
    {
        if (empty($this->detections)) {
            return [];
        }

        return array_map(function ($detection) {
            return [
                'class' => $detection['class'] ?? 'unknown',
                'confidence' => round(($detection['confidence'] ?? 0) * 100, 2),
                'bbox' => $detection['bbox'] ?? [],
                'has_segmentation' => !empty($detection['segmentation_mask'] ?? null)
            ];
        }, $this->detections);
    }

    /**
     * Get detection count
     */
    public function getDetectionCountAttribute(): int
    {
        return count($this->detections ?? []);
    }

    /**
     * Get processing status badge
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'success',
            'processing' => 'warning',
            'processing_requested' => 'info',
            'failed' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Scope for completed detections
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for recent detections
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope for specific RVM
     */
    public function scopeForRvm($query, $rvmId)
    {
        return $query->where('rvm_id', $rvmId);
    }
}
