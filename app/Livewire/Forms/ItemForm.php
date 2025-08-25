<?php

namespace App\Livewire\Forms;

use App\Models\RequisitionItem;
use Livewire\Attributes\Rule;
use Livewire\Form;

class ItemForm extends Form
{
    #[Rule(['required', 'exists:stocks,id'])]
    public $stock_id;

    #[Rule(['required', 'exists:requisitions,id'])]
    public $requisition_id;

    #[Rule(['required', 'min:1'])]
    public $requested_qty;

    public function fillForm(RequisitionItem $item): void {
        $this->stock_id = $item->stock_id;
        $this->requisition_id = $item->requisition_id;
        $this->requested_qty = $item->requested_qty;
    }

    public function toArray(): array {
        return [
            'stock_id' => $this->stock_id,
            'requisition_id' => $this->requisition_id,
            'requested_qty' => $this->requested_qty
        ];
    }

}
