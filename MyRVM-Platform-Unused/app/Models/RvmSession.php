<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RvmSession extends Model
{
    use HasFactory;

    protected $table = 'rvm_sessions';

    protected $fillable = [
        'id',
        'user_id',
        'rvm_id',
        'session_token',
        'status',
        'expires_at',
        'claimed_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'claimed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the RVM that owns the session.
     */
    public function rvm(): BelongsTo
    {
        return $this->belongsTo(ReverseVendingMachine::class, 'rvm_id');
    }
}
