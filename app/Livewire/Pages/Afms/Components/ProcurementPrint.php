<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Models\PurchaseRequest;
use App\Services\Afms\OrderService;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ProcurementPrint extends Component
{
    public PurchaseRequest $request;

    public $type;
    public $data = [];

    // meal forms
    public $unit;
    public $pax_qty;
    public $mealSnack;
    public $arrangement;
    public $delivery_date;
    public $menu;
    public $qty;
    public $unit_cost;

    public function mount(PurchaseRequest $request) {
        $this->request = $request;

    }

    public function addMeals() {

        $currentRequest = $this->request;

        $this->data[] = [
            'unit' => $this->unit,
            'pax_qty' => $this->pax_qty,
            'mealSnack' => $this->mealSnack,
            'arrangement' => $this->arrangement,
            'delivery_date' => $this->delivery_date,
            'menu' => $this->menu,
            'qty' => $this->qty,
            'unit_cost' => $this->unit_cost,
        ];

        $this->reset(['unit', 'pax_qty', 'mealSnack', 'arrangement', 'delivery_date', 'menu', 'qty', 'unit_cost']);

        $this->type = 'Meals';
        $this->request = $currentRequest;

    }

    public function printPO(OrderService $orderService) {
        if ($this->type == 'Meals') {
            return $orderService->handle('po_meal_template.xlsx', $this->data);
        }else if ($this->type == 'Travel') {
            return $orderService->handle('template', $this->data);
        }else if ($this->type == 'Ticket') {
            return $orderService->handle('template', $this->data);
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.components.procurement-print');
    }
}
