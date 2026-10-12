<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Procurement extends Model
{
    //
    protected $fillable = [
        'code',
        'notice_of_award',
        'project_title',
        'contract_signing',
        'source_of_funds',
        'estimated_budget_total',
        'estimated_budget_mooe',
        'estimated_budget_co',
        'pmo_end_user',
        'early_activity',
        'mode_of_procurement',
        'advertisement_posting',
        'submission_bids',
        'procurement_start',
        'procurement_end',
        'app_year',
        'remarks',
        'category',
        'quantity',
        'unit',
        'unit_cost',
        'total_abc',
        'remaining_quantity',
        'remaining_budget',
        'procurement_strategy',
        'procurement_status',
        'bid_evaluation_criteria',
    ];

    protected static function booted(): void
    {
        static::saving(function (Procurement $procurement) {
            if (!is_null($procurement->quantity) && !is_null($procurement->unit_cost)) {
                $procurement->total_abc = $procurement->quantity * $procurement->unit_cost;
            }
        });

        static::creating(function (Procurement $procurement) {
            if (is_null($procurement->remaining_budget) && !is_null($procurement->total_abc)) {
                $procurement->remaining_budget = $procurement->total_abc;
            }

            if (is_null($procurement->remaining_quantity) && !is_null($procurement->quantity)) {
                $procurement->remaining_quantity = $procurement->quantity;
            }
        });
    }

    public function employee() {
        return $this->belongsTo(Employee::class, 'end_user');
    }

    public function purchaseRequest() {
        return $this->hasOne(PurchaseRequest::class);
    }

    public function purchaseOrder() {
        return $this->hasOne(PurchaseOrder::class);
    }
}
