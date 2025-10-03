<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TimezoneSyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'device_type',
        'sync_type',
        'old_timezone',
        'new_timezone',
        'sync_timestamp',
        'status',
        'details',
        'ip_address',
        'country',
        'city',
        'sync_method'
    ];

    protected $casts = [
        'sync_timestamp' => 'datetime',
        'details' => 'array',
    ];

    /**
     * Get the device that owns the timezone sync log
     */
    public function device()
    {
        if ($this->device_type === 'rvm') {
            return $this->belongsTo(ReverseVendingMachine::class, 'device_id');
        }
        
        return null;
    }

    /**
     * Scope for RVM devices
     */
    public function scopeForRvm($query)
    {
        return $query->where('device_type', 'rvm');
    }

    /**
     * Scope for successful syncs
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for failed syncs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for recent syncs
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('sync_timestamp', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Get sync status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'success' => 'success',
            'failed' => 'danger',
            'pending' => 'warning',
            default => 'secondary'
        };
    }

    /**
     * Get sync type display name
     */
    public function getSyncTypeDisplayAttribute()
    {
        return match($this->sync_type) {
            'manual' => 'Manual Sync',
            'automatic' => 'Automatic Sync',
            'bulk' => 'Bulk Sync',
            'scheduled' => 'Scheduled Sync',
            default => ucfirst($this->sync_type)
        };
    }

    /**
     * Get timezone change description
     */
    public function getTimezoneChangeDescriptionAttribute()
    {
        if ($this->old_timezone && $this->new_timezone) {
            return "Changed from {$this->old_timezone} to {$this->new_timezone}";
        } elseif ($this->new_timezone) {
            return "Set to {$this->new_timezone}";
        }
        
        return 'Timezone sync';
    }
}