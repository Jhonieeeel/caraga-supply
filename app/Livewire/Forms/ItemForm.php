<?php

namespace App\Livewire\Forms;

use App\Actions\RequisitionItem\CreateItemAction;
use App\Actions\RequisitionItem\UpdateItemAction;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Stock;
use Livewire\Attributes\Rule;
use Livewire\Form;

class ItemForm extends Form
{

    #[Rule(['nullable', 'exists:requisitions,id'])]
    public $requisition_id;

    #[Rule(['nullable', 'exists:stocks,id'])]
    public $stock_id;

    #[Rule(['nullable', 'min:1'])]
    public $requested_qty;

    public array $selectedStockIds = [];
    public array $requestedItems = [];

    public function rules(): array
    {
        return [
            'selectedStockIds' => ['required', 'array'],
            'selectedStockIds.*' => ['integer', 'exists:stocks,id'],
        ];
    }

    public function update(UpdateItemAction $update_item_action, RequisitionItem $item)
    {
        $this->validate($update_item_action, $item);
        $update_item_action->handle($item, $this->toArray());
    }

    public function toArray(): array
    {
        return [
            'requisition_id' => $this->requisition_id,
            'stock_id' => $this->stock_id,
            'requested_qty' => $this->requested_qty
        ];
    }

    public function fillForm(RequisitionItem $item): void
    {
        $this->requisition_id = $item->requisition_id;
        $this->stock_id = $item->stock_id;
        $this->requested_qty = $item->requested_qty;
    }

    public function releaseItem(Requisition $requisition)
    {

        if ($requisition->completed) {
            foreach ($requisition->items as $item) {
                return Stock::find($item->stock_id)->decrement('quantity', $item->requested_qty);
            }
        }

        return;
    }

    public function create(CreateItemAction $create_item_action, Requisition $requisition)
    {
        $create_item_action->handle($requisition, $this->requestedItems);
        $this->reset();
        return;
    }
}
