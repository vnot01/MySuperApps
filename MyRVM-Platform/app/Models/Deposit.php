<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rvm_id',
        'session_token',
        'waste_type',
        'weight',
        'quantity',
        'quality_grade',
        'ai_confidence',
        'ai_analysis',
        'cv_confidence',
        'cv_analysis',
        'cv_waste_type',
        'cv_weight',
        'cv_quantity',
        'cv_quality_grade',
        'ai_waste_type',
        'ai_weight',
        'ai_quantity',
        'ai_quality_grade',
        'reward_amount',
        'status',
        'rejection_reason',
        'processed_at',
    ];

    protected $casts = [
        'weight' => 'decimal:3',
        'ai_confidence' => 'decimal:2',
        'cv_confidence' => 'decimal:2',
        'cv_weight' => 'decimal:3',
        'ai_weight' => 'decimal:3',
        'reward_amount' => 'decimal:2',
        'ai_analysis' => 'array',
        'cv_analysis' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Deposit ini dilakukan oleh satu User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Deposit ini dilakukan di satu RVM.
     */
    public function rvm(): BelongsTo
    {
        return $this->belongsTo(ReverseVendingMachine::class, 'rvm_id');
    }

    /**
     * Setiap deposit menghasilkan satu transaksi kredit.
     */
    public function transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, 'sourceable');
    }
}
