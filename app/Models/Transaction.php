<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    protected $fillable = [
        'stock_id',
        'requisition_id',
        'quantity',
        'type_of_transaction'
    ];

    public function stocks()
    {
        return $this->belongsTo(Stock::class);
    }

    public function requisitions()
    {
        return $this->belongsTo(Requisition::class);
    }
}
