<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrTransportationItem extends Model
{
    protected $fillable = [
        'pr_transportation_id',
        'pax_qty',
        'itinerary',
        'date_time',
        'no_of_vehicles',
        'estimated_unit_cost',
        'sort_order',
    ];

    protected $casts = [
        'pax_qty'             => 'integer',
        'no_of_vehicles'      => 'integer',
        'estimated_unit_cost' => 'float',
    ];

    public function transportation(): BelongsTo
    {
        return $this->belongsTo(PrTransportation::class, 'pr_transportation_id');
    }

    public function getEstimatedCostAttribute(): float
    {
        return $this->no_of_vehicles * $this->estimated_unit_cost;
    }
}
