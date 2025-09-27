<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RvmHealthLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'rvm_id',
        'payload',
        'status',
    ];

    public function rvm()
    {
        return $this->belongsTo(ReverseVendingMachine::class, 'rvm_id');
    }
}