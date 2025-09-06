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
        'sourceable_id',
        'sourceable_type',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'balance_before' => 'decimal:4',
        'balance_after' => 'decimal:4',
    ];

    /**
     * Transaksi ini dimiliki oleh satu User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Transaksi ini terkait dengan satu UserBalance.
     */
    public function userBalance(): BelongsTo
    {
        return $this->belongsTo(UserBalance::class);
    }

    /**
     * Mendapatkan model sumber dari transaksi (bisa Deposit atau VoucherRedemption).
     */
    public function sourceable(): MorphTo
    {
        return $this->morphTo();
    }
}
