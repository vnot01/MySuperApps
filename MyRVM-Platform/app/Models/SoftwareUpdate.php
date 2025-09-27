<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoftwareUpdate extends Model
{
    protected $fillable = [
        'rvm_id',
        'update_type',
        'current_version',
        'target_version',
        'update_source',
        'status',
        'progress',
        'progress_message',
        'started_at',
        'completed_at',
        'rollback_version',
        'rollback_reason',
        'error_message',
        'created_by'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'progress' => 'integer'
    ];

    public function rvm(): BelongsTo
    {
        return $this->belongsTo(ReverseVendingMachine::class, 'rvm_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}