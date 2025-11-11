<?php

namespace App\Livewire\Pages\Afms;

use App\Actions\Stock\CreateStockAction;
use App\Actions\Stock\EditStockAction;
use App\Actions\Transaction\CreateTransaction;
use App\Livewire\Forms\StockForm;
use App\Models\Stock;
use App\Models\Supply;
use Illuminate\Contracts\Database\Query\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Livewire\WithPagination;


#[Lazy()]
class StockTable extends Component
{

    use WithPagination;

    public $quantity = 5;
    public $search = '';

    public array $headers = [];

    // form
    public StockForm $stockForm;
    public Stock $stock;


    public function mount()
    {
        $this->headers = [
            ['index' => 'barcode', 'label' => 'Barcode'],
            ['index' => 'supply.name', 'label' => 'Supply'],
            ['index' => 'stock_number', 'label' => 'Stock No.'],
            ['index' => 'quantity', 'label' => 'Quantity'],
            ['index' => 'price', 'label' => 'Price'],
            ['index' => 'stock_location', 'label' => 'Location'],
            ['index' => 'action'],
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    #[Computed()]
    public function rows()
    {
        return Stock::query()
            ->with('supply:id,name')
            ->where('quantity', '>=', 1)
            ->when($this->search, function ($query) {
                $query->whereHas('supply', function (Builder $supplyQuery) {
                    return $supplyQuery->where('name', 'like', "{$this->search}%");
                });
            })
            ->paginate($this->quantity)
            ->withQueryString();
    }

    #[Computed()]
    public function getSupplies()
    {
        return Supply::all(['id', 'name'])
            ->map(fn($supply) => [
                'label' => $supply->name,
                'value' => $supply->id,
            ]);
    }

    // purchase
    public function savePurchaseStock(CreateTransaction $create_transaction)
    {
        $stock = $this->stockForm->createPurchaseOrder($create_transaction);

        $this->dispatch('modal:partial-edit-stock-close');

        $this->dispatch('alert', [
            'text' => 'Purchase Order Created Successfully.',
            'color' => 'teal',
            'title' => 'Success'
        ]);

        return;
    }
    public function selectStock(Stock $stock)
    {
        $this->stockForm->partialForm($stock);
        $this->dispatch('modal:partial-edit-stock-open');
    }

    // C R U D
    // create
    public function create(CreateStockAction $create_stock_action)
    {
        $this->stockForm->create($create_stock_action);
        $this->dispatch('modal:add-close');
    }

    // edit
    public function edit(Stock $stock)
    {
        $this->stock = $stock;
        $this->stockForm->fillForm($stock);
        $this->dispatch('modal:edit-stock-open');
    }

    // update
    public function update(EditStockAction $edit_stock_action)
    {
        $stock = $this->stockForm->update($this->stock, $edit_stock_action);
        $this->dispatch('modal:edit-stock-close');
        $this->dispatch('refresh', id: $stock->id);
    }

    // delete
    public function delete($id)
    {
        Stock::findOrFail($id)->delete();
        $this->dispatch('refresh', id: $id);
    }

    #[Computed('refresh')]
    public function updateList($id = null) {}

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.stock-table');
    }
}
