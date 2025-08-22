<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    protected $fillable = [
        'ris',
        'requested_by',
        'approved_by',
        'issued_by',
        'received_by',
        'pdf'
    ];

    public function items()
    {
        return $this->hasMany(RequisitionItem::class);
    }
}
