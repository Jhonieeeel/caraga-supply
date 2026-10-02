<?php

namespace App\Livewire\Forms;

use App\Actions\RequisitionItem\AllocateStockToRequisitionAction;
use App\Actions\RequisitionItem\UpdateItemAction;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Stock;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Rule;
use Livewire\Form;

class ItemForm extends Form
{

    #[Rule(['nullable', 'exists:requisitions,id'])]
    public $requisition_id;

    #[Rule(['nullable', 'exists:stocks,id'])]
    public $stock_id;

    #[Rule(['nullable', 'integer', 'min:1'])]
    public $requested_qty;

    public array $selectedStockIds = [];
    public array $requestedItems = [];

    public function update(UpdateItemAction $update_item_action, RequisitionItem $item)
    {
        $this->validate([
            'requested_qty' => ['required', 'integer', 'min:1'],
        ]);

        $update_item_action->handle($item, (int) $this->requested_qty);

        return;
    }

    /**
     * Livewire's FormObjectSynth dehydrates a form through toArray(), so every
     * property that must survive a round-trip (e.g. quantities typed into the
     * request modal while searching/paginating) has to be listed here.
     */
    public function toArray(): array
    {
        return [
            'requisition_id' => $this->requisition_id,
            'stock_id' => $this->stock_id,
            'requested_qty' => $this->requested_qty,
            'selectedStockIds' => $this->selectedStockIds,
            'requestedItems' => $this->requestedItems,
        ];
    }

    public function fillForm(RequisitionItem $item): void
    {
        $this->requisition_id = $item->requisition_id;
        $this->stock_id = $item->stock_id;
        $this->requested_qty = $item->requested_qty;
    }

    /**
     * Validates every requested line BEFORE anything is created/allocated:
     * blank inputs are ignored, every other quantity must be a whole number
     * >= 1, and the total requested per supply must not exceed what that
     * supply has on hand across its lots (allocation spills across lots).
     *
     * @return array<int, int> stock_id => quantity
     */
    public function validatedItems(): array
    {
        $key = $this->getPropertyName() . '.requestedItems';

        $items = collect($this->requestedItems)
            ->reject(fn ($qty) => $qty === null || $qty === '');

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([$key => 'Please add at least one item.']);
        }

        $stocks = Stock::with('supply:id,name')->whereIn('id', $items->keys())->get()->keyBy('id');

        $errors = [];
        $perSupply = [];

        foreach ($items as $stockId => $qty) {
            $stock = $stocks->get($stockId);

            if (! $stock) {
                $errors[] = 'One of the selected stocks no longer exists.';
                continue;
            }

            if (filter_var($qty, FILTER_VALIDATE_INT) === false || (int) $qty < 1) {
                $errors[] = "The quantity for {$stock->supply->name} ({$stock->stock_number}) must be a whole number of at least 1.";
                continue;
            }

            $perSupply[$stock->supply_id] = ($perSupply[$stock->supply_id] ?? 0) + (int) $qty;
        }

        foreach ($perSupply as $supplyId => $requested) {
            $available = (int) Stock::where('supply_id', $supplyId)->sum('quantity');

            if ($requested > $available) {
                $name = $stocks->firstWhere('supply_id', $supplyId)->supply->name;
                $errors[] = "Only {$available} {$name} available, but {$requested} requested.";
            }
        }

        if ($errors) {
            throw ValidationException::withMessages([$key => implode(' ', $errors)]);
        }

        return $items->map(fn ($qty) => (int) $qty)->all();
    }

    public function create(AllocateStockToRequisitionAction $allocate_stock_action, Requisition $requisition, ?array $items = null)
    {
        $items ??= $this->validatedItems();

        $allocate_stock_action->handle($requisition, $items);
        $this->reset();
        return;
    }
}
