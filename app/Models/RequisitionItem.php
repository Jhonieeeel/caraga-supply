<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitionItem extends Model
{
    protected $fillable = [
        'requisition_id',
        'stock_id',
        'requested_qty'
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }
}
