<?php

namespace App\Livewire\Pages\Afms;

use App\Livewire\Forms\OrderForm;
use App\Livewire\Forms\RequestForm;
use App\Models\Procurement;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class ShowData extends Component
{
    use WithFileUploads;
    public OrderForm $orderForm;
    public RequestForm $requestForm;

    public Procurement $procurement;
    public PurchaseOrder $purchaseOrder;
    public PurchaseRequest $purchaseRequest;

    public function mount($id) {
        $this->procurement = Procurement::with(['purchaseRequest', 'purchaseOrder'])->find( $id );
    }

    public function printRequest() {
        dd($this->procurement->purchaseRequest);
    }

    public function editOrder(PurchaseOrder $purchaseOrder) {
        $this->purchaseOrder = $purchaseOrder;
        $this->orderForm->fillform($this->purchaseOrder);
        $this->dispatch('modal:update-order-open');
    }

    public function editRequest(PurchaseRequest $purchaseRequest) {
        $this->purchaseRequest = $purchaseRequest;
        $this->requestForm->fillform($this->purchaseRequest);
        $this->dispatch('modal:update-request-open');
    }



    public function printOrder() {
        dd($this->procurement->purchaseOrder);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        // $test = PurchaseRequest::find(1);
        // dd($test);
        return view('livewire.pages.afms.show-data');
    }
}
