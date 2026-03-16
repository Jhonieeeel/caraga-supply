<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrWorkbookBlock extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'sort_order',
        'block_title',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function items()
    {
        return $this->hasMany(PrWorkbookItem::class, 'workbook_block_id')
            ->orderBy('sort_order');
    }


}
