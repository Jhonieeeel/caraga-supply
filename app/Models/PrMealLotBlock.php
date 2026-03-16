<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrMealLotBlock extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'sort_order',
        'lot_title',
        'location',
        'delivery_period',
    ];

    public function request()
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function items()
    {
        return $this->hasMany(PrMealItem::class, 'lot_block_id')->orderBy('sort_order');
    }

    public function accommodations()
    {
        return $this->hasMany(PrAccommodationBlock::class, 'lot_block_id')->orderBy('sort_order');
    }
}
