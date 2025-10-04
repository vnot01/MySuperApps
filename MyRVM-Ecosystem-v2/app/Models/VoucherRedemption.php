<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'voucher_id',
        'discount_amount',
        'purchase_amount'
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'purchase_amount' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function getFormattedDiscountAmountAttribute(): string
    {
        return number_format($this->discount_amount, 2);
    }

    public function getFormattedPurchaseAmountAttribute(): string
    {
        return number_format($this->purchase_amount, 2);
    }

    public function getSavingsPercentageAttribute(): float
    {
        if ($this->purchase_amount == 0) {
            return 0;
        }

        return ($this->discount_amount / $this->purchase_amount) * 100;
    }
}
