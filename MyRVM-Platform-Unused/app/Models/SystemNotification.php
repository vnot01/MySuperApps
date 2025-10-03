<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type',
        'priority',
        'target_audience',
        'target_user_ids',
        'action_url',
        'action_text',
        'scheduled_at',
        'expires_at',
        'is_active',
        'created_by',
        'total_sent',
        'total_read',
    ];

    protected $casts = [
        'target_user_ids' => 'array',
        'scheduled_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $dates = [
        'scheduled_at',
        'expires_at',
    ];

    /**
     * Get the user who created this notification
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if notification is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if notification should be sent now
     */
    public function shouldBeSent(): bool
    {
        return $this->is_active && 
               (!$this->scheduled_at || $this->scheduled_at->isPast()) &&
               !$this->isExpired();
    }

    /**
     * Get priority color for UI
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'critical' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'green',
            default => 'gray'
        };
    }

    /**
     * Get type icon for UI
     */
    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'system' => 'cog',
            'maintenance' => 'wrench',
            'security' => 'shield',
            'feature' => 'star',
            'performance' => 'chart-line',
            'alert' => 'exclamation-triangle',
            'info' => 'info-circle',
            default => 'bell'
        };
    }

    /**
     * Scope for active notifications
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for notifications that should be sent
     */
    public function scopeShouldBeSent($query)
    {
        return $query->active()
                    ->where(function($q) {
                        $q->whereNull('scheduled_at')
                          ->orWhere('scheduled_at', '<=', now());
                    })
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope for specific priority
     */
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for specific type
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }
}
