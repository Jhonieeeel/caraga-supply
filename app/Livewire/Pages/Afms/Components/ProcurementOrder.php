<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Models\PurchaseOrder;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ProcurementOrder extends Component
{
    public $tab = 'Orders';

    public ?string $search = '';
    public ?int $quantity = 5;

    public array $headers = [];

    public function mount()
    {
        $this->headers = [
            ['index' => 'procurement.code' ,'label'=> 'Code PAP'],
            ['index' => 'procurement.project_title' ,'label'=> 'Project Title'],
            ['index' => 'po_number' ,'label'=> 'PO Number'],
            ['index' => 'supplier' ,'label'=> 'Supplier'],
            ['index' => 'procurement.mode_of_procurement' ,'label'=> 'Mode of Procurement'],
            ['index' => 'action' ,'label'=> 'Action'],
        ];
    }

    // search =  po_number, title, supplier,

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


    public function render()
    {
        return view('livewire.pages.afms.components.procurement-order');
    }
}
