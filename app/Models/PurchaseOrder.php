<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{

    protected $fillable = [
        'noa',
        'variance',
        'po_number',
        'date_posted',
        'po_date',
        'delivery_date',
        'ntp',
        'supplier',
        'contact_price',
        'email_link',
        'resolution_number',
        'abc_based_app',
        'abc',
    ];

    public function purchaseRequest() {
        return $this->belongsTo(PurchaseRequest::class);
    }
}
