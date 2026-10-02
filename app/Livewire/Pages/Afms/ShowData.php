<?php

namespace App\Livewire\Pages\Afms;

use App\Actions\Procurement\UpdateOrder;
use App\Actions\Procurement\UpdateRequest;
use App\Livewire\Forms\OrderForm;
use App\Livewire\Forms\RequestForm;
use App\Models\Procurement;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\DB;
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

    public ?Procurement $procurement = null;
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

    public function mount($id = null)
    {
        $this->authorize('manage-procurement');

        $this->procurement = Procurement::with(['purchaseRequest', 'purchaseOrder'])->findOrFail($id);
    }
    public function deleteOrder(PurchaseOrder $order) {
        $this->dispatch('procurement-tab', 'Annual');

        return DB::transaction(function () use ($order) {
            $this->refundOrder($order);

            return $order->delete();
        });
    }

    public function deleteRequest(PurchaseRequest $request) {
        $this->dispatch('procurement-tab', 'Annual');

        return DB::transaction(function () use ($request) {
            // the database cascade-deletes every PO pointing at this PR, so refund them first
            PurchaseOrder::where('purchase_request_id', $request->id)
                ->orWhere('abc', $request->id)
                ->orWhere('date_posted', $request->id)
                ->get()
                ->each(fn (PurchaseOrder $order) => $this->refundOrder($order));

            return $request->delete();
        });
    }

    private function refundOrder(PurchaseOrder $order): void
    {
        if ($order->procurement_id && !is_null($order->contract_price)) {
            $procurement = Procurement::find($order->procurement_id);

            if ($procurement && !is_null($procurement->remaining_budget)) {
                $procurement->increment('remaining_budget', (float) $order->contract_price);
            }
        }
    }

    public function redirectRequest() {
        $request = $this->procurement->purchaseRequest;
        return redirect()->route('print-pr', ['request' => $request]); // or just $request if model binding
    }

    public function redirectOrder() {
        $request = $this->procurement->purchaseRequest;
        return redirect()->route('print-po', ['request' => $request]);
    }



    #[Computed]
    public function variance(): float
    {
        // Use safe navigation to avoid "property on null"
        $purchaseOrder = $this->procurement?->purchaseOrder;

        // Exit early if there's no purchase order
        if (!$purchaseOrder) {
            return 0;
        }

        // Use the correct foreign key (adjust if needed)
        $purchaseRequest = PurchaseRequest::find($purchaseOrder->purchase_request_id);

        if (!$purchaseRequest) {
            return 0;
        }

        // Return computed variance safely
        return (float) $purchaseRequest->abc - (float) $this->orderForm?->contract_price;
    }



    public function editOrder(PurchaseOrder $purchaseOrder) {
        $this->purchaseOrder = $purchaseOrder;
        $this->orderForm->fillform($this->purchaseOrder);
        $this->dispatch('modal:update-order-open');
    }

    public function submitEditOrder(UpdateOrder $updateOrder, PurchaseOrder $purchaseOrder) {

        if ($this->updateNtpFile) {
            if ($this->purchaseOrder->ntp_pdf_file && Storage::disk('public')->exists($this->purchaseOrder->ntp_pdf_file)) {
                Storage::disk('public')->delete($this->purchaseOrder->ntp_pdf_file);
            }

            $this->orderForm->ntp_pdf_file = $this->updateNtpFile;
        }else {
            $this->orderForm->currentNtpFile = $this->purchaseOrder->ntp_pdf_file;
        }

        if ($this->updateNoaFile) {
            if ($this->purchaseOrder->noa_pdf_file && Storage::disk('public')->exists($this->purchaseOrder->noa_pdf_file)) {
                Storage::disk('public')->delete($this->purchaseOrder->noa_pdf_file);
            }
            $this->orderForm->noa_pdf_file = $this->updateNoaFile;
        }else {
            $this->orderForm->currentNoaFile = $this->purchaseOrder->noa_pdf_file;
        }

        if ($this->updatePoFile) {
            if ($this->purchaseOrder->po_pdf_file && Storage::disk('public')->exists($this->purchaseOrder->po_pdf_file)) {
                Storage::disk('public')->delete($this->purchaseOrder->po_pdf_file);
            }
            $this->orderForm->po_pdf_file = $this->updatePoFile;
        }else {
            $this->orderForm->currentPoFile = $this->purchaseOrder->po_pdf_file;
        }

        if ($this->updateResoFile) {
            if ($this->purchaseOrder->reso_pdf_file && Storage::disk('public')->exists($this->purchaseOrder->reso_pdf_file)) {
                Storage::disk('public')->delete($this->purchaseOrder->reso_pdf_file);
            }
            $this->orderForm->reso_pdf_file = $this->updateResoFile;
        }else {
            $this->orderForm->currentResoFile = $this->purchaseOrder->reso_pdf_file;
        }

        // Same formula as ProcurementOrder::variance(): PR abc - contract price.
        $purchaseRequest = PurchaseRequest::find($this->purchaseOrder->purchase_request_id);
        $this->orderForm->variance = $purchaseRequest
            ? (float) $purchaseRequest->abc - (float) $this->orderForm->contract_price
            : $this->purchaseOrder->variance;
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
            if ($this->purchaseRequest->philgeps_pdf_file && Storage::disk('public')->exists($this->purchaseRequest->philgeps_pdf_file)) {
                Storage::disk('public')->delete($this->purchaseRequest->philgeps_pdf_file);
            }
            $this->requestForm->philgeps_pdf_file = $this->updatePhilFile;
        }else {
            $this->requestForm->currentPhilGepsFile = $this->purchaseRequest->philgeps_pdf_file;
        }

        if ($this->updateAppFile) {
            if ($this->purchaseRequest->app_spp_pdf_file && Storage::disk('public')->exists($this->purchaseRequest->app_spp_pdf_file)) {
                Storage::disk('public')->delete($this->purchaseRequest->app_spp_pdf_file);
            }
            $this->requestForm->app_spp_pdf_file = $this->updateAppFile;
        }else {
            $this->requestForm->currentAppFile = $this->purchaseRequest->app_spp_pdf_file;
        }

        $this->dispatch('modal:update-request-close');

        return $this->requestForm->update($updateRequest, $this->purchaseRequest);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.show-data');
    }
}
