<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    protected $fillable = [
        'ris',
        'user_id',
        'requested_by',
        'approved_by',
        'issued_by',
        'received_by',
        'completed',
        'pdf'
    ];

    public function requestedBy()
    {
        return $this->belongsTo(User::class, "requested_by");
    }
    public function approvedBy()
    {
        return $this->belongsTo(User::class, "approved_by");
    }
    public function issuedBy()
    {
        return $this->belongsTo(User::class, "issued_by");
    }
    public function receivedBy()
    {
        return $this->belongsTo(User::class, "received_by");
    }

    public function items()
    {
        return $this->hasMany(RequisitionItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
