<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Annual extends Model
{
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
        'app_year',
        'remarks',
    ];

    public function procurement() {
        return $this->belongsTo(Procurement::class);
    }

    public function order() {
        return $this->hasOne(PurchaseOrder::class);
    }
}
