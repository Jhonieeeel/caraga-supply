<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{

    protected $fillable = [
        'procurement_id',
        'purchase_request_id',
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
    public function procurement() {
        return $this->belongsTo(Procurement::class);
    }

    public function purchaseRequest() {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function datePosted() {
        return $this->belongsTo(PurchaseRequest::class, 'date_posted');
    }

    public function abc() {
        return $this->belongsTo(PurchaseRequest::class, 'abc');
    }
}
