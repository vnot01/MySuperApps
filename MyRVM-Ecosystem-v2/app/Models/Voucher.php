<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'min_purchase',
        'max_discount',
        'usage_limit',
        'used_count',
        'valid_from',
        'valid_until',
        'is_active'
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function redemptions(): HasMany
    {
        return $this->hasMany(VoucherRedemption::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('valid_from', '<=', now())
                    ->where('valid_until', '>=', now());
    }

    public function scopeAvailable($query)
    {
        return $query->active()->where(function ($q) {
            $q->whereNull('usage_limit')
              ->orWhereRaw('used_count < usage_limit');
        });
    }

    public function isAvailable(): bool
    {
        return $this->is_active &&
               $this->valid_from <= now() &&
               $this->valid_until >= now() &&
               (is_null($this->usage_limit) || $this->used_count < $this->usage_limit);
    }

    public function calculateDiscount(float $purchaseAmount): float
    {
        if (!$this->isAvailable() || $purchaseAmount < $this->min_purchase) {
            return 0;
        }

        $discount = $this->discount_type === 'percentage' 
            ? ($purchaseAmount * $this->discount_value / 100)
            : $this->discount_value;

        if ($this->max_discount && $discount > $this->max_discount) {
            $discount = $this->max_discount;
        }

        return min($discount, $purchaseAmount);
    }

    public function redeem(int $userId, float $purchaseAmount): VoucherRedemption
    {
        if (!$this->isAvailable()) {
            throw new \Exception('Voucher is not available');
        }

        if ($purchaseAmount < $this->min_purchase) {
            throw new \Exception('Purchase amount is below minimum requirement');
        }

        $discountAmount = $this->calculateDiscount($purchaseAmount);

        $redemption = $this->redemptions()->create([
            'user_id' => $userId,
            'discount_amount' => $discountAmount,
            'purchase_amount' => $purchaseAmount
        ]);

        $this->increment('used_count');

        return $redemption;
    }

    public function getUsagePercentageAttribute(): float
    {
        if (!$this->usage_limit) {
            return 0;
        }

        return ($this->used_count / $this->usage_limit) * 100;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->valid_until < now();
    }

    public function getIsFullyUsedAttribute(): bool
    {
        return $this->usage_limit && $this->used_count >= $this->usage_limit;
    }
}
