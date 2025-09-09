<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TmpTransaction extends Model
{
    use HasFactory;

    protected $table = 'tmp_transactions';

    protected $fillable = [
        'username',
        'phone',
        'amount',
        'transaction_date',
        'entry_by',
    ];
}
