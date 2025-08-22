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
        'price',
    ];

    public function supply()
    {
        return $this->belongsTo(Supply::class);
    }
}
