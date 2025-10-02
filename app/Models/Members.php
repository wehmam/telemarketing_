<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Members extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'members';
    protected $fillable = [
        'name',
        'username',
        'phone',
        'nama_rekening',
        'marketing_id',
        'team_id',
        'batch_code',
        'import_at',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Relasi ke marketing (user yang menambahkan member)
     */
    public function marketing()
    {
        return $this->belongsTo(User::class, 'marketing_id');
    }

    /**
     * Relasi ke transaksi-transaksi member
     */
    // public function transactions()
    // {
    //     return $this->hasMany(Transaction::class, 'member_id');
    // }

    /**
     * Relasi ke team (lewat user marketing)
     */
    public function team()
    {
        return $this->hasOneThrough(
            Team::class,       // model terakhir yang mau diambil
            TeamMember::class, // tabel pivot user -> team
            'user_id',         // foreign key di team_members
            'id',              // primary key di teams
            'marketing_id',    // foreign key di members
            'team_id'          // foreign key di team_members
        );
    }

    /**
     * Relasi ke TRANSACTIONS (TRANSACTIONS USER)
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'member_id');
    }

    public function setPhoneAttribute($value)
    {
        $phone = trim($value);

        if (preg_match('/^0/', $phone)) {
            $phone = preg_replace('/^0/', '62', $phone);
        }

        if (preg_match('/^\+62/', $phone)) {
            $phone = preg_replace('/^\+62/', '62', $phone);
        }

        $this->attributes['phone'] = $phone;
    }

    // change to be 0 at the beginning
    public function getPhoneLocalAttribute()
    {
        return preg_replace('/^62/', '0', $this->phone);
    }

    public function followups()
    {
        return $this->hasManyThrough(
            TransactionFollowup::class,
            Transaction::class,
            'member_id',        // Foreign key on transactions table
            'transaction_id',   // Foreign key on transaction_followups table
            'id',               // Local key on members table
            'id'                // Local key on transactions table
        );
    }

    public function lastTransaction()
    {
        return $this->hasOne(\App\Models\Transaction::class, 'member_id')
            ->latest('transaction_date')
            ->latest('created_at');
    }
}
