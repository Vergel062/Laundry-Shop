<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'fullname', 'phone', 'email', 'password', 'role', 
        'branch_id', 'referral_code', 'referred_by', 'profile_pic', 
        'is_archived', 'archive_date'
    ];

    protected $hidden = ['password'];

    public function branch() {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function transactions() {
        return $this->hasMany(Transaction::class, 'user_id');
    }
}