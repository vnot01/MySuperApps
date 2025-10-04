<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_balance_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'sourceable_type',
        'sourceable_id'
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'balance_before' => 'decimal:4',
        'balance_after' => 'decimal:4'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userBalance(): BelongsTo
    {
        return $this->belongsTo(UserBalance::class);
    }

    public function sourceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeCredit($query)
    {
        return $query->where('type', 'credit');
    }

    public function scopeDebit($query)
    {
        return $query->where('type', 'debit');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function getFormattedAmountAttribute(): string
    {
        $sign = $this->type === 'credit' ? '+' : '-';
        return $sign . number_format($this->amount, 2);
    }

    public function getFormattedBalanceBeforeAttribute(): string
    {
        return number_format($this->balance_before, 2);
    }

    public function getFormattedBalanceAfterAttribute(): string
    {
        return number_format($this->balance_after, 2);
    }
}
