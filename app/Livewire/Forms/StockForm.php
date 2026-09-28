<?php

namespace App\Livewire\Forms;

use App\Actions\Stock\CreateStockAction;
use App\Actions\Stock\EditStockAction;
use App\Domain\Gasu\Aggregates\SupplyAggregate;
use App\Models\Stock;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class StockForm extends Form
{

    #[Rule(['required', 'exists:supplies,id'])]
    public $supply_id;

    #[Validate(['required'])]
    public $barcode;

    #[Rule(['required',])]
    public $stock_number;

    #[Rule(['required', 'numeric'])]
    public $quantity;

    public $initial_quantity;

    #[Rule(['min:0', 'numeric'])]
    public $price;

    #[Rule('required')]
    public $stock_location;

    public function createPurchaseOrder()
    {
        $this->validate([
            'stock_number' => ['required'],
            'quantity' => ['required', 'numeric'],
        ]);

        $stock = Stock::where('stock_number', $this->stock_number)->firstOrFail();
        $supply = $stock->supply;

        SupplyAggregate::retrieve($supply->uuid)
            ->receiveStock($stock->stock_number, (int) $this->quantity, Auth::id())
            ->persist();

        $this->reset();

        return;
    }

    public function create(CreateStockAction $create_stock_action)
    {
        $this->validate();

        $this->initial_quantity = $this->quantity;
        $stock = $create_stock_action->handle($this->toArray());

        $this->reset();

        return $stock;
    }

    public function update(Stock $stock, EditStockAction $edit_stock_action)
    {
        $this->validate();
        $stock = $edit_stock_action->handle($stock, $this->toArray());
        $this->reset();

        return $stock;
    }

    public function fillForm(Stock $stock): void
    {
        $this->supply_id = $stock->supply_id;
        $this->barcode = $stock->barcode;
        $this->stock_number = $stock->stock_number;
        $this->quantity = $stock->quantity;
        $this->price = $stock->price;
        $this->stock_location = $stock->stock_location;
        $this->initial_quantity = $stock->initial_quantity;
    }

    public function updatePartial(Stock $stock, EditStockAction $edit_stock_action)
    {
        $this->validate();
        $edit_stock_action->handle($stock, $this->toArray());
        $this->reset();
    }

    public function partialForm(Stock $stock): void
    {
        $this->supply_id = $stock->supply_id;
        $this->stock_number = $stock->stock_number;
        $this->barcode = $stock->barcode;
        $this->stock_location = $stock->stock_location;
        $this->price = $stock->price;
        $this->quantity = null;
    }

    public function toArray(): array
    {
        return [
            'supply_id' => $this->supply_id,
            'barcode' => $this->barcode,
            'stock_number' => $this->stock_number,
            'quantity' => $this->quantity,
            'initial_quantity' => $this->initial_quantity,
            'price' => $this->price,
            'stock_location' => $this->stock_location
        ];
    }
}
