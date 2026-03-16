<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrWorkbookItem extends Model
{
    protected $fillable = [
        'workbook_block_id',
        'sort_order',
        'particular',
        'delivery_date',
        'qty',
        'unit',
        'estimated_unit_cost',
    ];

    public function block()
    {
        return $this->belongsTo(PrWorkbookBlock::class, 'workbook_block_id');
    }
}
