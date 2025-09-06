<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'member_id',
        'user_id',
        'amount',
        'transaction_date',
        'type',
        'username',
        'phone',
    ];

    protected static function booted()
    {
        static::creating(function ($transaction) {
            if (empty($transaction->id)) {
                $transaction->id = (string) Str::uuid();
            }
        });
    }

    public function member()
    {
        return $this->belongsTo(Members::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
