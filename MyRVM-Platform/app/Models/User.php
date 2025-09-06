<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable // implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'tenant_id',
        'google_id',
        'line_id',
        'discord_id',
        'avatar',
        'phone_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the URL to the user's avatar.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar && Storage::disk('s3')->exists($this->avatar)) {
            return Storage::disk('s3')->url($this->avatar);
        }
        // Gunakan nama disk 'minio' yang sudah kita konfigurasi
        // if ($this->avatar && Storage::disk('s3')->exists($this->avatar)) {
        //     // Use Storage::url to get the public URL if configured
        //     return Storage::url($this->avatar);
        // }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name);
    }

    /**
     * User ini dimiliki oleh satu Role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * User ini (jika bukan super admin) dimiliki oleh satu Tenant.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * User memiliki satu saldo.
     */
    public function balance(): HasOne
    {
        return $this->hasOne(UserBalance::class);
    }

    /**
     * User memiliki banyak transaksi.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * User memiliki banyak deposit.
     */
    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    /**
     * User memiliki banyak penukaran voucher.
     */
    public function voucherRedemptions(): HasMany
    {
        return $this->hasMany(VoucherRedemption::class);
    }
}
