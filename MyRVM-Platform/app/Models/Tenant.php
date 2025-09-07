<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Tenant memiliki banyak user (misalnya, admin tenant dan staff tenant).
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Tenant menawarkan banyak voucher.
     */
    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

}
