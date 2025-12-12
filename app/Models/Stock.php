<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'supply_id',
        'barcode',
        'stock_number',
        'quantity',
        'initial_quantity',
        'price',
        'stock_location'
    ];

    public function supply()
    {
        return $this->belongsTo(Supply::class);
    }

    public function items()
    {
        return $this->belongsTo(RequisitionItem::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

}
