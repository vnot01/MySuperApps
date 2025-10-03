<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RvmConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'rvm_id',
        'config_key',
        'config_value',
        'config_type',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config_value' => 'array' // For JSON config values
    ];

    public function rvm(): BelongsTo
    {
        return $this->belongsTo(ReverseVendingMachine::class, 'rvm_id');
    }

    public function getTypedValueAttribute()
    {
        $value = $this->config_value;
        
        switch ($this->config_type) {
            case 'integer':
                return (int) $value;
            case 'boolean':
                return (bool) $value;
            case 'json':
                return is_string($value) ? json_decode($value, true) : $value;
            case 'float':
                return (float) $value;
            default:
                return $value;
        }
    }

    public function setTypedValue($value)
    {
        switch ($this->config_type) {
            case 'json':
                $this->config_value = is_array($value) ? json_encode($value) : $value;
                break;
            case 'boolean':
                $this->config_value = $value ? '1' : '0';
                break;
            default:
                $this->config_value = (string) $value;
        }
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByKey($query, $key)
    {
        return $query->where('config_key', $key);
    }
}
