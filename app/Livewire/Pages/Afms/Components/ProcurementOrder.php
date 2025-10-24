<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Actions\Procurement\CreateOrder;
use App\Livewire\Forms\OrderForm;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ProcurementOrder extends Component
{
    public $tab = 'Orders';

    public OrderForm $orderForm;

    public ?string $search = '';
    public ?int $quantity = 5;

    public array $headers = [];

    public ?int $purchase_order_id = null;

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

    // search =  po_number, title, supplier,

    public function onSubmit(CreateOrder $createOrder) {
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

    public function render()
    {
        return view('livewire.pages.afms.components.procurement-order');
    }
}
