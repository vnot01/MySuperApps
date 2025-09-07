<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReverseVendingMachine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location_description',
        'status',
        'api_key',
        'last_status_change',
        'admin_access_pin',
        'remote_access_enabled',
        'kiosk_mode_enabled',
        'pos_settings',
    ];

    protected $casts = [
        'pos_settings' => 'array',
        'remote_access_enabled' => 'boolean',
        'kiosk_mode_enabled' => 'boolean',
        'last_status_change' => 'datetime',
    ];

    /**
     * RVM ini memiliki banyak histori deposit.
     */
    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class, 'rvm_id');
    }

    /**
     * RVM ini memiliki banyak session.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(RvmSession::class, 'rvm_id');
    }
}
