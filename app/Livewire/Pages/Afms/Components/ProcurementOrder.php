<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Actions\Procurement\CreateOrder;
use App\Livewire\Forms\OrderForm;
use App\Models\Procurement;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProcurementOrder extends Component
{
    use WithFileUploads;
    public $tab = 'Orders';

    public OrderForm $orderForm;

    public ?PurchaseRequest $purchaseRequest;

    public ?string $search = '';
    public ?int $quantity = 5;

    public array $headers = [];

    public ?int $purchase_order_id = null;

    public $varianceResult;

    public $po_pdf_file;


    public function mount()
    {
        $this->headers = [
            ['index' => 'po_number' ,'label'=> 'PO Number'],
            ['index' => 'procurement.project_title' ,'label'=> 'Project Title'],
            ['index' => 'ntp' ,'label'=> 'Ntp'],
            ['index' => 'noa' ,'label'=> 'Noa'],
            ['index' => 'resolution_number' ,'label'=> 'Resolution Number'],
            ['index' => 'action' ,'label'=> 'Action'],
        ];
    }

    #[On('procurement-order-refresh')]
    public function updateComponent() {
    }

    public function viewDetails(Procurement $procurement) {
        return redirect()->route('pmu.show', $procurement->id);
    }

    public function onSubmit(CreateOrder $createOrder) {
        $this->orderForm->variance = $this->varianceResult;
        $this->orderForm->po_pdf_file = $this->po_pdf_file;
        $this->orderForm->procurement_id = $this->purchaseRequest->procurement_id;
        $this->orderForm->abc_based_app = $this->purchaseRequest->procurement_id;
        $this->orderForm->abc = $this->purchaseRequest->id;
        $this->orderForm->date_posted = $this->purchaseRequest->id;
        return $this->orderForm->submit($createOrder);
    }


    #[Computed()]
    public function rows()
    {
        return PurchaseOrder::query()
            ->when($this->search, function ($query) {
                $query->where(function ($order) {
                    $order->where('po_number', 'like', '%' . $this->search . '%')
                    ->orWhere('supplier', 'like', '%' . $this->search . '%')
                    ->orWhereHas('procurement', function ($procurement) {
                        $procurement->where('project_title', 'like', '%' . $this->search . '%');
                    });
                });
            })
            ->paginate($this->quantity)
            ->withQueryString();
    }

    #[Computed()]
    public function getPr() {
        return PurchaseRequest::all(['id', 'pr_number'])
            ->map(fn($request) => [
                'label' => $request->pr_number ?? 'No PR yet',
                'value' => $request->id,
            ]);
    }

    #[Computed()]
    public function variance()
    {
        $this->purchaseRequest = PurchaseRequest::find($this->orderForm->purchase_request_id);
        if ($this->purchaseRequest) {
            $this->varianceResult = (float) $this->purchaseRequest->abc - (float) $this->orderForm->contract_price;
            return $this->varianceResult;
        }

        return null;
    }

    public function render()
    {
        return view('livewire.pages.afms.components.procurement-order');
    }
}
