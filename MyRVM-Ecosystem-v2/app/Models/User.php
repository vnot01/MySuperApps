<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Economy System Relationships
    public function balance(): HasOne
    {
        return $this->hasOne(UserBalance::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function voucherRedemptions(): HasMany
    {
        return $this->hasMany(VoucherRedemption::class);
    }

    // Economy System Methods
    public function getBalance(): float
    {
        return $this->balance?->balance ?? 0.0;
    }

    public function addBalance(float $amount, string $description = null): Transaction
    {
        if (!$this->balance) {
            $this->balance()->create([
                'balance' => 0,
                'currency' => 'IDR'
            ]);
            $this->load('balance');
        }

        return $this->balance->addBalance($amount, $description);
    }

    public function deductBalance(float $amount, string $description = null): Transaction
    {
        if (!$this->balance) {
            throw new \Exception('User has no balance account');
        }

        return $this->balance->deductBalance($amount, $description);
    }

    public function getFormattedBalanceAttribute(): string
    {
        return $this->balance?->formatted_balance ?? '0.00 IDR';
    }
}
