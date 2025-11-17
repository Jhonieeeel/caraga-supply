<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    protected $fillable = [
        'stock_id',
        'requisition_id',
        'quantity',
        'type_of_transaction',
        'rsmi_file',
        'rpci_file',
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
