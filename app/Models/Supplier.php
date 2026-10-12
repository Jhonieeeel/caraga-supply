<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'business_name',
        'tin',
        'contact_person',
        'email',
        'phone',
        'address',
        'philgeps_no',
        'philgeps_membership',
        'is_eligible',
        'eligibility_checked_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_eligible' => 'boolean',
            'eligibility_checked_at' => 'datetime',
        ];
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
