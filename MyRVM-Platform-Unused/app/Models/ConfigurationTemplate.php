<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfigurationTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'config_data',
        'version',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'config_data' => 'array',
        'is_active' => 'boolean'
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}