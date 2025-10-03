<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class VoucherRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'voucher_id',
        'redemption_code',
        'redeemed_at',
        'used_at',
        'cost_at_redemption',
    ];

    protected $casts = [
        'cost_at_redemption' => 'decimal:4',
        'redeemed_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Penukaran ini dimiliki oleh satu User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Penukaran ini terkait dengan satu Voucher.
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    /**
     * Setiap penukaran voucher menghasilkan satu transaksi debit.
     */
    public function transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, 'sourceable');
    }
}
