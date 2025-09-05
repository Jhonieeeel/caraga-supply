<?php

namespace App\Livewire\Forms;

use App\Actions\Stock\CreateStockAction;
use App\Actions\Stock\EditStockAction;
use App\Actions\Transaction\CreateTransaction;
use App\Models\Stock;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class StockForm extends Form
{

    #[Rule(['required', 'exists:supplies,id'])]
    public $supply_id;

    #[Validate(['required'])]
    public $barcode;

    #[Rule(['required'])]
    public $stock_number;

    #[Rule(['required', 'numeric'])]
    public $quantity;

    public $initial_quantity;

    #[Rule(['required', 'numeric'])]
    public $price;

    public function createPurchaseOrder(CreateTransaction $create_transaction)
    {
        $this->validate();

        $stock = Stock::where('stock_number', $this->stock_number)->firstOrFail();
        $stock->increment('quantity', $this->quantity);

        $create_transaction->handle([
            'stock_id' => $stock->id,
            'quantity' => $this->quantity,
            'type_of_transaction' => 'PO'
        ]);

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
        $edit_stock_action->handle($stock, $this->toArray());
        $this->reset();
    }

    public function fillForm(Stock $stock): void
    {
        $this->supply_id = $stock->supply_id;
        $this->barcode = $stock->barcode;
        $this->stock_number = $stock->stock_number;
        $this->quantity = $stock->quantity;
        $this->price = $stock->price;
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
    }

    public function toArray(): array
    {
        return [
            'supply_id' => $this->supply_id,
            'barcode' => $this->barcode,
            'stock_number' => $this->stock_number,
            'quantity' => $this->quantity,
            'initial_quantity' => $this->initial_quantity,
            'price' => $this->price
        ];
    }
}
