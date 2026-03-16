<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrAccommodationBlock extends Model
{
    protected $fillable = [
        'lot_block_id',
        'sort_order',
        'accommodation_title',
        'location',
        'date',
    ];

    public function lotBlock()
    {
        return $this->belongsTo(PrMealLotBlock::class, 'lot_block_id');
    }

    public function items()
    {
        return $this->hasMany(PrAccommodationItem::class, 'accommodation_block_id')->orderBy('sort_order');
    }
}
