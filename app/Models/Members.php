<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Members extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'username',
        'phone',
        'nama_rekening',
        'marketing_id',
        'team_id'
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
        // return $this->hasMany(Transactions::class);
    }
}
