<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyPoint extends Model
{
    protected $table = 'loyalty_points';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'points_earned', 'points_redeemed', 
        'source', 'transaction_id'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transaction() {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}
