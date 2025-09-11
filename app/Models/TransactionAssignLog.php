<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionAssignLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'action_by',
        'from_member_id',
        'to_user_id',
        'moved_count',
    ];
}
