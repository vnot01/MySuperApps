<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiModel extends Model
{
    protected $fillable = [
        'rvm_id',
        'model_name',
        'model_version',
        'model_path',
        'model_size',
        'model_checksum',
        'model_url',
        'is_active',
        'deployed_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'deployed_at' => 'datetime',
        'model_size' => 'integer'
    ];

    public function rvm(): BelongsTo
    {
        return $this->belongsTo(ReverseVendingMachine::class, 'rvm_id');
    }
}