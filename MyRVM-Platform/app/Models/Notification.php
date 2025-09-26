<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_id',
        'user_id',
        'title',
        'message',
        'type',
        'category',
        'data',
        'read_at',
        'is_system_wide',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'is_system_wide' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if notification is read.
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(): void
    {
        if (!$this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Scope for unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope for user notifications.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhere('is_system_wide', true);
        });
    }

    /**
     * Create a new notification.
     */
    public static function createNotification(array $data): self
    {
        return self::create([
            'notification_id' => $data['notification_id'] ?? uniqid('notif_'),
            'user_id' => $data['user_id'] ?? null,
            'title' => $data['title'],
            'message' => $data['message'],
            'type' => $data['type'] ?? 'info',
            'category' => $data['category'] ?? 'system',
            'data' => $data['data'] ?? null,
            'is_system_wide' => $data['is_system_wide'] ?? false,
        ]);
    }
}
