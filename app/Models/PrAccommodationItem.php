<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrAccommodationItem extends Model
{
    protected $fillable = [
        'accommodation_block_id',
        'sort_order',
        'no_days',
        'room_type',
        'room_arrangement',
        'inclusive_dates',
        'remarks',
        'other_requirement',
        'qty',
        'unit',
        'estimated_unit_cost',
    ];

    public function accommodationBlock()
    {
        return $this->belongsTo(PrAccommodationBlock::class, 'accommodation_block_id');
    }
}
