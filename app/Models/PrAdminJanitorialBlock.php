<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrAdminJanitorialBlock extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'block_title',
        'delivery_period',
        'delivery_site',
        'sort_order',
    ];

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrAdminJanitorialItem::class)->orderBy('sort_order');
    }

    public function administrativeItems(): HasMany
    {
        return $this->hasMany(PrAdminJanitorialItem::class)
            ->where('item_group', 'administrative')
            ->orderBy('sort_order');
    }

    public function janitorialItems(): HasMany
    {
        return $this->hasMany(PrAdminJanitorialItem::class)
            ->where('item_group', 'janitorial')
            ->orderBy('sort_order');
    }
}
