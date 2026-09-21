<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory';
    public $timestamps = false;

    protected $fillable = [
        'branch_id', 'item_name', 'stock_level', 'alert_level', 
        'unit', 'min_threshold'
    ];

    public function branch() {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
