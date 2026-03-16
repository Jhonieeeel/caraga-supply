<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrAdminJanitorialItem extends Model
{
    protected $fillable = [
        'pr_admin_janitorial_block_id',
        'item_group',
        'item_name',
        'quantity',
        'unit',
        'estimated_unit_cost',
        'sort_order',
    ];

    protected $casts = [
        'quantity'             => 'integer',
        'estimated_unit_cost'  => 'float',
    ];

    public function block(): BelongsTo
    {
        return $this->belongsTo(PrAdminJanitorialBlock::class, 'pr_admin_janitorial_block_id');
    }

    public function getEstimatedCostAttribute(): float
    {
        return $this->quantity * $this->estimated_unit_cost;
    }
}
