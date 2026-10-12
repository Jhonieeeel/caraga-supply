<?php

namespace App\Livewire\Forms;

use App\Actions\Stock\CreateStockAction;
use App\Actions\Stock\EditStockAction;
use App\Domain\Gasu\Aggregates\SupplyAggregate;
use App\Domain\Gasu\Exceptions\DuplicateStockLotException;
use App\Models\Stock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule as ValidationRule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class StockForm extends Form
{
    // stocks.price is decimal(15,2)
    public const MAX_PRICE = 9999999999999.99;

    public const MAX_QUANTITY = 1000000;

    // id of the stock lot selected for a purchase order (stock numbers are
    // only unique per supply, so the lot is never looked up by number alone)
    public $stock_id;

    #[Rule(['required', 'exists:supplies,id'])]
    public $supply_id;

    #[Validate(['required', 'string', 'max:255'])]
    public $barcode;

    #[Rule(['required', 'string', 'max:255'])]
    public $stock_number;

    #[Rule(['required', 'integer', 'min:0', 'max:' . self::MAX_QUANTITY])]
    public $quantity;

    public $initial_quantity;

    #[Rule(['required', 'numeric', 'min:0', 'max:' . self::MAX_PRICE])]
    public $price;

    #[Rule(['required', 'string', 'max:255'])]
    public $stock_location;

    public function createPurchaseOrder()
    {
        $this->validate([
            'stock_id' => ['required', 'exists:stocks,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:' . self::MAX_QUANTITY],
        ]);

        $stock = Stock::with('supply')->findOrFail($this->stock_id);
        $supply = $stock->supply;

        SupplyAggregate::retrieve($supply->uuid)
            ->receiveStock($stock->stock_number, (int) $this->quantity, Auth::id())
            ->persist();

        $this->reset();

        return;
    }

    public function create(CreateStockAction $create_stock_action)
    {
        // Stock numbers are unique per supply; check before anything is
        // recorded in the event store.
        $this->validate(array_merge($this->getRules(), [
            'stock_number' => [
                'required', 'string', 'max:255',
                ValidationRule::unique('stocks', 'stock_number')->where('supply_id', $this->supply_id),
            ],
        ]));

        $this->initial_quantity = $this->quantity;

        try {
            $stock = $create_stock_action->handle($this->toArray());
        } catch (DuplicateStockLotException) {
            throw ValidationException::withMessages([
                $this->getPropertyName() . '.stock_number' => 'This stock number already exists for the selected supply.',
            ]);
        }

        $this->reset();

        return $stock;
    }

    /**
     * Metadata only (barcode / location / price). Quantity changes go
     * through purchase orders (receiveStock) and requisitions; supply and
     * stock number identify the lot in the event stream, so they are shown
     * read-only in the edit modal and are not accepted here.
     */
    public function update(Stock $stock, EditStockAction $edit_stock_action)
    {
        $this->validate([
            'barcode' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', 'max:' . self::MAX_PRICE],
            'stock_location' => ['required', 'string', 'max:255'],
        ]);

        $stock = $edit_stock_action->handle($stock, [
            'barcode' => $this->barcode,
            'price' => $this->price,
            'stock_location' => $this->stock_location,
        ]);
        $this->reset();

        return $stock;
    }

    public function fillForm(Stock $stock): void
    {
        $this->stock_id = $stock->id;
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
        $this->update($stock, $edit_stock_action);
    }

    public function partialForm(Stock $stock): void
    {
        $this->stock_id = $stock->id;
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
            'stock_id' => $this->stock_id,
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
