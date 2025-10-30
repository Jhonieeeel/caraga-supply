<?php

namespace App\Livewire\Forms;

use App\Actions\Procurement\CreateOrder;
use App\Actions\Procurement\UpdateOrder;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Storage;
use Livewire\Form;

class OrderForm extends Form
{
    public $procurement_id;
    public $purchase_request_id;
    public $order_number;
    public $noa;
    public $variance;
    public $po_number;
    public $date_posted;
    public $po_date;
    public $delivery_date;
    public $ntp;
    public $resolution_number;
    public $supplier;
    public $contract_price;
    public $email_link;
    public $abc_based_app;
    public $abc;

    // upload file
    public $ntp_pdf_file;
    public $noa_pdf_file;
    public $po_pdf_file;
    public $reso_pdf_file;

    // store files
    public $new_ntp_pdf_file;
    public $new_noa_pdf_file;
    public $new_po_pdf_file;
    public $new_reso_pdf_file;


    // update
    public $currentNtpFile;
    public $currentNoaFile;
    public $currentPoFile;
    public $currentResoFile;

    protected function rules(): array
    {
        return [
            // Foreign keys
            'procurement_id'       => ['required', 'exists:procurements,id'],
            'purchase_request_id'  => ['required', 'exists:purchase_requests,id'],
            'date_posted'          => ['nullable', 'exists:purchase_requests,id'],
            'abc_based_app'        => ['nullable', 'exists:procurements,id'],
            'abc'                  => ['nullable', 'exists:purchase_requests,id'],

            // Basic fields
            'variance'             => ['nullable', 'numeric'],
            'po_number'            => ['nullable', 'string'],
            'resolution_number'    => ['nullable', 'string'],
            'supplier'             => ['nullable', 'string'],
            'contract_price'       => ['nullable', 'numeric'],
            'email_link'           => ['nullable', 'email'],

            // Date fields
            'po_date'              => ['nullable', 'date'],
            'delivery_date'        => ['nullable', 'date'],
            'ntp'                  => ['nullable', 'date'],
            'noa'                  => ['nullable', 'date'],


            // files
            'ntp_pdf_file'         => ['nullable',  'file', 'mimes:pdf'],
            'noa_pdf_file'         => ['nullable',  'file', 'mimes:pdf'],
            'po_pdf_file'          => ['nullable',  'file', 'mimes:pdf'],
            'reso_pdf_file'        => ['nullable',  'file', 'mimes:pdf'],
        ];
    }

    public function submit(CreateOrder $createOrder): PurchaseOrder {

        $this->validate();

        if ($this->po_pdf_file) {
            $this->new_po_pdf_file = $this->po_pdf_file->store('po-records', 'public');
        }

        return $createOrder->handle($this->toArray());
    }

    public function update(UpdateOrder $updateOrder, PurchaseOrder $purchaseOrder): PurchaseOrder {

        $this->validate();

        $this->new_ntp_pdf_file = $this->ntp_pdf_file ? $this->ntp_pdf_file->store('po-records', 'public') : $this->currentNtpFile;

        $this->new_po_pdf_file = $this->po_pdf_file ? $this->po_pdf_file->store('po-records', 'public') : $this->currentPoFile;

        $this->new_noa_pdf_file = $this->noa_pdf_file ? $this->noa_pdf_file->store('po-records', 'public') : $this->currentNoaFile;

        $this->new_reso_pdf_file = $this->reso_pdf_file ? $this->reso_pdf_file->store('po-records', 'public') : $this->currentResoFile;

        return $updateOrder->handle($purchaseOrder, $this->toArray());

    }

    public function fillform(PurchaseOrder $order) {
        $this->procurement_id = $order->procurement_id;
        $this->purchase_request_id = $order->purchase_request_id;
        $this->noa = $order->noa;
        $this->variance = $order->variance;
        $this->po_number = $order->po_number;
        $this->date_posted = $order->purchase_request_id;
        $this->po_date = $order->po_date;
        $this->delivery_date = $order->delivery_date;
        $this->ntp = $order->ntp;
        $this->resolution_number = $order->resolution_number;
        $this->supplier = $order->supplier;
        $this->contract_price = $order->contract_price;
        $this->email_link = $order->email_link;
        $this->abc_based_app = $order->procurement_id;
        $this->abc = $order->purchase_request_id;

        // file
        $this->ntp_pdf_file = null;
        $this->noa_pdf_file = null;
        $this->po_pdf_file = null;
        $this->reso_pdf_file = null;

    }

    public function toArray() {
        return [
            'procurement_id' => $this->procurement_id,
            'purchase_request_id' => $this->purchase_request_id,
            'noa' => $this->noa,
            'variance' => $this->variance,
            'po_number' => $this->po_number,
            'date_posted' => $this->date_posted,
            'po_date' => $this->po_date,
            'delivery_date' => $this->delivery_date,
            'ntp' => $this->ntp,
            'resolution_number' => $this->resolution_number,
            'supplier' => $this->supplier,
            'contract_price' => $this->contract_price,
            'email_link' => $this->email_link,
            'abc_based_app' => $this->abc_based_app,
            'abc' => $this->abc,
            'ntp_pdf_file' => $this->new_ntp_pdf_file,
            'noa_pdf_file' => $this->new_noa_pdf_file,
            'reso_pdf_file' => $this->new_reso_pdf_file,
            'po_pdf_file' => $this->new_po_pdf_file
        ];
    }

}
