<?php

namespace App\Livewire\Forms;

use App\Actions\Procurement\CreateOrder;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Livewire\Attributes\Validate;
use Livewire\Form;

class OrderForm extends Form
{
    public ?int $procurement_id = null;
    public ?int $purchase_request_id = null;
    public ?string $order_number = '';
    public ?Carbon $noa = null;
    public ?float $variance = null;
    public ?string $po_number = '';
    public ?int $date_posted = null;
    public ?Carbon $po_date = null;
    public ?Carbon $delivery_date = null;
    public ?Carbon $ntp = null;
    public ?Carbon $resolution_number = null;
    public ?string $supplier = '';
    public ?float $contact_price = null;
    public ?string $email_link = '';
    public ?int $abc_based_app = null;
    public ?int $abc = null;


    protected function rules(): array  {
        return [
            'procurement_id' => 'required',
            'purchase_request_id' => 'required',
            'order_number' => 'nullable',
            'noa' => 'nullable',
            'variance' => 'nullable',
            'po_number' => 'nullable',
            'date_posted' => 'nullable',
            'po_date' => 'nullable',
            'delivery_date' => 'nullable',
            'ntp' => 'nullable',
            'resolution_number' => 'nullable',
            'supplier' => 'nullable',
            'contact_price' => 'nullable',
            'email_link' => 'nullable',
            'abc_based_app' => 'nullable',
            'abc' => 'nullable'
        ];
    }

    public function submit(CreateOrder $createOrder): PurchaseOrder {
        $this->validate();
        return $createOrder->handle($this->toArray());
    }

    public function fillform(PurchaseOrder $order) {
        $this->procurement_id = $order->procurement_id;
        $this->purchase_request_id = $order->purchase_request_id;
        $this->order_number = $order->order_number;
        $this->noa = $order->noa;
        $this->variance = $order->variance;
        $this->po_number = $order->po_number;
        $this->date_posted = $order->date_posted;
        $this->po_date = $order->date_posted;
        $this->delivery_date = $order->delivery_date;
        $this->ntp = $order->ntp;
        $this->resolution_number = $order->resolution_number;
        $this->supplier = $order->supplier;
        $this->contact_price = $order->contact_price;
        $this->email_link = $order->email_link;
        $this->abc_based_app = $order->abc_based_app;
        $this->abc = $order->abc?->id;
    }

    public function toArray() {
        return [
            'procurement_id' => $this->procurement_id,
            'purchase_request_id' => $this->purchase_request_id,
            'order_number' => $this->order_number,
            'noa' => $this->noa,
            'variance' => $this->variance,
            'po_number' => $this->po_number,
            'date_posted' => $this->date_posted,
            'po_date' => $this->po_date,
            'delivery_date' => $this->delivery_date,
            'ntp' => $this->ntp,
            'resolution_number' => $this->resolution_number,
            'supplier' => $this->supplier,
            'contact_price' => $this->contact_price,
            'email_link' => $this->email_link,
            'abc_based_app' => $this->abc_based_app,
            'abc' => $this->abc
        ];
    }

}
