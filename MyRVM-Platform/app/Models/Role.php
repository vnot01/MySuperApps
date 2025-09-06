<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * Role memiliki banyak user.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Role memiliki banyak permission.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }
}
