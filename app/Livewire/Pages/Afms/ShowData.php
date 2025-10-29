<?php

namespace App\Livewire\Pages\Afms;

use App\Actions\Procurement\UpdateOrder;
use App\Actions\Procurement\UpdateRequest;
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


    // new file PR
    public  $updateAppFile;
    public $updatePhilFile;

    // new file PO
    public $updateNtpFile;
    public $updateNoaFile;
    public $updatePoFile;
    public $updateResoiFile;


    public function mount($id) {
        $this->procurement = Procurement::with(['purchaseRequest', 'purchaseOrder'])->find( $id );
    }

    public function printRequest() {
        dd($this->procurement->purchaseRequest);
    }

    public function editOrder(PurchaseOrder $purchaseOrder) {
        $this->dispatch('modal:update-order-open');
        $this->purchaseOrder = $purchaseOrder;
        $this->orderForm->fillform($this->purchaseOrder);
    }

    public function editRequest(PurchaseRequest $purchaseRequest) {
        $this->purchaseRequest = $purchaseRequest;
        $this->requestForm->fillform($this->purchaseRequest);
        $this->dispatch('modal:update-request-open');
    }

    public function submitEditOrder(UpdateOrder $updateOrder, PurchaseOrder $purchaseOrder) {
        if ($this->updateNtpFile) {
            $this->orderForm->ntp_pdf_file = $this->updateNtpFile;
        }

        if ($this->updateNoaFile) {
            $this->orderForm->noa_pdf_file = $this->updateNoaFile;
        }

        if ($this->updatePoFile) {
            $this->orderForm->po_pdf_file = $this->updatePoFile;
        }

        if ($this->updateResoFile) {
            $this->orderForm->reso_pdf_file = $this->updateResoFile;
        }

        $this->orderForm->update($updateOrder, $purchaseOrder);
    }
    public function submitEditRequest(UpdateRequest $updateRequest) {

        if ($this->updateAppFile) {
            $this->requestForm->app_spp_pdf_file = $this->updateAppFile;
        }

        if ($this->updatePhilFile) {
            $this->requestForm->philgeps_pdf_file = $this->updatePhilFile;
        }

        $this->dispatch('modal:update-request-close');

        return $this->requestForm->update($updateRequest, $this->purchaseRequest);
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
