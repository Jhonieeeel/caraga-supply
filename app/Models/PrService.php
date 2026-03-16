<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrService extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'delivery_period',
        'delivery_site',
        'quantity',
        'unit',
        'estimated_unit_cost',
        'technical_specifications',
    ];

    protected $casts = [
        'quantity'             => 'integer',
        'estimated_unit_cost'  => 'float',
    ];

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function getEstimatedCostAttribute(): float
    {
        return $this->quantity * $this->estimated_unit_cost;
    }
}
