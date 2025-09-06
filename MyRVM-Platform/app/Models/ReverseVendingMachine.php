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
    ];

    /**
     * RVM ini memiliki banyak histori deposit.
     */
    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class, 'rvm_id');
    }
}
