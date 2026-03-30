<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrAccommodationItem extends Model
{
    protected $fillable = [
        'accommodation_block_id',
        'sort_order',
        'no_of_pax',
        'room_requirement',
        'no_of_rooms',
        'check_in',
        'check_out',
        'no_of_nights',
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
