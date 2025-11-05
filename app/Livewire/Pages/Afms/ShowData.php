<?php

namespace App\Livewire\Pages\Afms;

use App\Actions\Procurement\UpdateOrder;
use App\Actions\Procurement\UpdateRequest;
use App\Livewire\Forms\OrderForm;
use App\Livewire\Forms\RequestForm;
use App\Models\Procurement;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
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
    public $updateAppFile;
    public $updatePhilFile;

    // new file PO
    public $updateNtpFile;
    public $updateNoaFile;
    public $updatePoFile;
    public $updateResoFile;

    // PO
    public $varianceResult;

    public function mount($id = null)
    {
        $this->procurement = $id
            ? Procurement::with(['purchaseRequest', 'purchaseOrder'])->find($id)
            : null;
    }


    #[Computed()]
    public function variance()
    {
        $this->purchaseRequest = PurchaseRequest::find($this->procurement->purchaseOrder->id);
        if ($this->purchaseRequest) {
            $this->varianceResult = (float) $this->purchaseRequest->abc - (float) $this->orderForm->contract_price;
            return $this->varianceResult;
        }

        return null;
    }

    public function printRequest() {
$this->updateAppFile();
    }

    public function editOrder(PurchaseOrder $purchaseOrder) {
        $this->purchaseOrder = $purchaseOrder;
        $this->orderForm->fillform($this->purchaseOrder);
        $this->dispatch('modal:update-order-open');
    }

    public function submitEditOrder(UpdateOrder $updateOrder, PurchaseOrder $purchaseOrder) {

        if ($this->updateNtpFile) {
            if ($this->purchaseOrder->ntp_pdf_file && Storage::exists($this->purchaseOrder->ntp_pdf_file)) {
                Storage::delete($this->purchaseOrder->ntp_pdf_file);
            }

            $this->orderForm->ntp_pdf_file = $this->updateNtpFile;
        }else {
            $this->orderForm->currentNtpFile = $this->purchaseOrder->ntp_pdf_file;
        }

        if ($this->updateNoaFile) {
            if ($this->purchaseOrder->noa_pdf_file && Storage::exists($this->purchaseOrder->noa_pdf_file)) {
                Storage::delete($this->purchaseOrder->noa_pdf_file);
            }
            $this->orderForm->noa_pdf_file = $this->updateNoaFile;
        }else {
            $this->orderForm->currentNoaFile = $this->purchaseOrder->noa_pdf_file;
        }

        if ($this->updatePoFile) {
            if ($this->purchaseOrder->po_pdf_file && Storage::exists($this->purchaseOrder->po_pdf_file)) {
                Storage::delete($this->purchaseOrder->po_pdf_file);
            }
            $this->orderForm->po_pdf_file = $this->updatePoFile;
        }else {
            $this->orderForm->currentPoFile = $this->purchaseOrder->po_pdf_file;
        }

        if ($this->updateResoFile) {
            if ($this->purchaseOrder->reso_pdf_file && Storage::exists($this->purchaseOrder->reso_pdf_file)) {
                Storage::delete($this->purchaseOrder->reso_pdf_file);
            }
            $this->orderForm->reso_pdf_file = $this->updateResoFile;
        }else {
            $this->orderForm->currentResoFile = $this->purchaseOrder->reso_pdf_file;
        }

        $this->orderForm->variance = $this->varianceResult;
        $this->dispatch('modal:update-order-close');

        return $this->orderForm->update($updateOrder, $this->purchaseOrder);
    }

    // Purchase Request
    public function editRequest(PurchaseRequest $purchaseRequest) {
        $this->purchaseRequest = $purchaseRequest;
        $this->requestForm->fillform($this->purchaseRequest);
        $this->dispatch('modal:update-request-open');
    }

    public function submitEditRequest(UpdateRequest $updateRequest) {
        if ($this->updatePhilFile) {
            if ($this->purchaseRequest->philgeps_pdf_file && Storage::exists($this->purchaseRequest->philgeps_pdf_file)) {
                Storage::disk('public')->delete($this->purchaseRequest->philgeps_pdf_file);
            }
            $this->requestForm->philgeps_pdf_file = $this->updatePhilFile;
        }else {
            $this->requestForm->currentPhilGepsFile = $this->purchaseRequest->philgeps_pdf_file;
        }

        if ($this->updateAppFile) {
            if ($this->purchaseRequest->app_spp_pdf_file && Storage::exists($this->purchaseRequest->app_spp_pdf_file)) {
                Storage::disk('public')->delete($this->purchaseRequest->app_spp_pdf_file);
            }
            $this->requestForm->app_spp_pdf_file = $this->updateAppFile;
        }else {
            $this->requestForm->currentAppFile = $this->purchaseRequest->app_spp_pdf_file;
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
        return view('livewire.pages.afms.show-data');
    }
}
