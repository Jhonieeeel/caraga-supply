<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{

    protected $cast = [
        'closing_date' => 'date'
    ];

    protected $fillable = [
        'closing_date',
        'input_date',
        'app_spp_pdf_file',
        'app_spp_pdf_filename',
        'philgeps_pdf_file',
        'philgeps_pdf_filename',
        'pr_number',
        'abc_based_app',
        'abc',
        'email_posting',
        'date_posted',
        'select_year',
    ];

    public function procurement() {
        return $this->belongsTo(Procurement::class);
    }

    public function AbcBasedApp() {
        return $this->belongsTo(Procurement::class, 'abc_based_app');
    }

    public function AppYear() {
        return $this->belongsTo(Procurement::class, 'app_year');
    }

    public function purchaseOrder() {
        return $this->hasOne(PurchaseOrder::class);
    }
}
