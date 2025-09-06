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
        'item_type_detected',
        'item_condition',
        'confidence_score',
        'local_ai_result',
        'gemini_validated',
        'gemini_response',
        'reward_value',
        'image_path',
        'deposited_at',
    ];

    protected $casts = [
        'reward_value' => 'decimal:4',
        'confidence_score' => 'float',
        'gemini_validated' => 'boolean',
        'local_ai_result' => 'array',
        'gemini_response' => 'array',
        'deposited_at' => 'datetime',
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
