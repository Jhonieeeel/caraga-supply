<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrTransportationBlock extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'delivery_period',
        'delivery_site',
        'pick_up',
        'reqs_vehicle',
        'reqs_model',
        'reqs_number',
        'sort_order',
    ];

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrTransportationItem::class)->orderBy('sort_order');
    }
}
