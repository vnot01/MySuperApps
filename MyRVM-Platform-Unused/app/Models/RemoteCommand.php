<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RemoteCommand extends Model
{
    protected $fillable = [
        'rvm_id',
        'command_type',
        'command_name',
        'command_payload',
        'status',
        'executed_by',
        'executed_at',
        'completed_at',
        'result',
        'error_message'
    ];

    protected $casts = [
        'command_payload' => 'array',
        'result' => 'array',
        'executed_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    public function rvm(): BelongsTo
    {
        return $this->belongsTo(ReverseVendingMachine::class, 'rvm_id');
    }

    public function executedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }
}