<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationMetric extends Model
{
    protected $fillable = [
        'rvm_id',
        'software_version',
        'ai_model_version',
        'ai_model_path',
        'uptime_seconds',
        'deposit_count_since_restart',
        'last_deposit_time',
        'error_count',
        'warning_count',
        'recorded_at'
    ];

    protected $casts = [
        'last_deposit_time' => 'datetime',
        'recorded_at' => 'datetime',
        'uptime_seconds' => 'integer',
        'deposit_count_since_restart' => 'integer',
        'error_count' => 'integer',
        'warning_count' => 'integer'
    ];

    public function rvm(): BelongsTo
    {
        return $this->belongsTo(ReverseVendingMachine::class, 'rvm_id');
    }
}
