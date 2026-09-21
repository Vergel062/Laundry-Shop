<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $table = 'expenses';
    public $timestamps = false;

    protected $fillable = [
        'branch_id', 'staff_id', 'expense_type', 'amount', 
        'remarks', 'date'
    ];

    public function branch() {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function staff() {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
