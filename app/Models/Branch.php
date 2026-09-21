<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'branches';
    public $timestamps = false; // Dahil custom timestamp ang 'created_at' at walang 'updated_at'

    protected $fillable = ['branch_name', 'location', 'archive_date'];

    public function users() {
        return $this->hasMany(User::class, 'branch_id');
    }

    public function transactions() {
        return $this->hasMany(Transaction::class, 'branch_id');
    }

    public function inventory() {
        return $this->hasMany(Inventory::class, 'branch_id');
    }

    public function expenses() {
        return $this->hasMany(Expense::class, 'branch_id');
    }
}
