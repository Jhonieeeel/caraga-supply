<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrMealItem extends Model
{
    protected $fillable = [
        'lot_block_id',
        'sort_order',
        'pax_qty',
        'meal_snack',
        'arrangement',
        'delivery_date',
        'menu',
        'other_requirement',
        'qty',
        'unit',
        'estimated_unit_cost',
    ];

    public function lotBlock()
    {
        return $this->belongsTo(PrMealLotBlock::class, 'lot_block_id');
    }
}
