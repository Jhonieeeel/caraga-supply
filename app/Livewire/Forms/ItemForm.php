<?php

namespace App\Livewire\Forms;

use App\Actions\RequisitionItem\CreateItemAction;
use App\Models\Requisition;
use Livewire\Form;

class ItemForm extends Form
{

    public array $selectedStockIds = [];
    public array $requestedItems = [];

    public function rules(): array
    {
        return [
            'selectedStockIds' => ['required', 'array'],
            'selectedStockIds.*' => ['integer', 'exists:stocks,id'],
        ];
    }

    public function create(CreateItemAction $create_item_action, Requisition $requisition)
    {
        $create_item_action->handle($requisition, $this->requestedItems);
        $this->reset();
        return;
    }
}
