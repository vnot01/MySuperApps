<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NetworkInformation extends Model
{
    protected $fillable = [
        'rvm_id',
        'local_ip',
        'virtual_ip',
        'gateway_ip',
        'dns_servers',
        'network_interface',
        'connection_type',
        'signal_strength',
        'last_network_check',
        'recorded_at'
    ];

    protected $casts = [
        'last_network_check' => 'datetime',
        'recorded_at' => 'datetime',
        'signal_strength' => 'integer'
    ];

    public function rvm(): BelongsTo
    {
        return $this->belongsTo(ReverseVendingMachine::class, 'rvm_id');
    }
}
