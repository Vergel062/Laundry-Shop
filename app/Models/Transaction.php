<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'staff_id', 'branch_id', 'ref_number', 'weight_kg', 
        'service_type', 'total_amount', 'payment_method', 'proof_image', 
        'payment_reference', 'proof_of_payment', 'order_status', 
        'payment_status', 'reference_number', 'date_paid'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function branch() {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
