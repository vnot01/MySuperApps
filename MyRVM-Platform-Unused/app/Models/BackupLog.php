<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'rvm_id',
        'backup_type',
        'file_path',
        'file_size',
        'upload_status',
        'minio_path',
        'backup_details',
        'backup_started_at',
        'backup_completed_at',
        'upload_started_at',
        'upload_completed_at',
        'error_message'
    ];

    protected $casts = [
        'backup_details' => 'array',
        'backup_started_at' => 'datetime',
        'backup_completed_at' => 'datetime',
        'upload_started_at' => 'datetime',
        'upload_completed_at' => 'datetime',
        'file_size' => 'integer'
    ];

    public function rvm(): BelongsTo
    {
        return $this->belongsTo(ReverseVendingMachine::class, 'rvm_id');
    }

    public function getDurationAttribute(): ?int
    {
        if ($this->backup_started_at && $this->backup_completed_at) {
            return $this->backup_started_at->diffInSeconds($this->backup_completed_at);
        }
        return null;
    }

    public function getUploadDurationAttribute(): ?int
    {
        if ($this->upload_started_at && $this->upload_completed_at) {
            return $this->upload_started_at->diffInSeconds($this->upload_completed_at);
        }
        return null;
    }

    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function scopeByType($query, $type)
    {
        return $query->where('backup_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('upload_status', $status);
    }

    public function scopeCompleted($query)
    {
        return $query->where('upload_status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('upload_status', 'failed');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
