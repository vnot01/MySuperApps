<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RemoteAccessSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'rvm_id',
        'admin_id',
        'start_time',
        'end_time',
        'status',
        'ip_address',
        'port',
        'reason'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime'
    ];

    public function rvm(): BelongsTo
    {
        return $this->belongsTo(ReverseVendingMachine::class, 'rvm_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && is_null($this->end_time);
    }

    public function getDurationAttribute(): ?int
    {
        if ($this->end_time) {
            return $this->start_time->diffInSeconds($this->end_time);
        }
        
        return $this->start_time->diffInSeconds(now());
    }
}
