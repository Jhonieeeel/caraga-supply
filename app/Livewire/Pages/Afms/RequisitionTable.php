<?php

namespace App\Livewire\Pages\Afms;

use App\Models\Stock;
use Illuminate\Contracts\Database\Query\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\QueryBuilder\QueryBuilder;

class RequisitionTable extends Component
{
    use WithPagination;

    public $headers = [];
    public $quantity = 5;
    public $search = '';

    // requestedItmes
    public array $selectedStockIds = [];
    public array $requestedQuantity = [];

    public function mount() {
        $this->headers = [
            ['index' => 'id', 'label' => 'Stock ID'],
            ['index' => 'supply.name', 'label' => 'Supply name'],
            ['index' => 'quantity', 'label' => 'Stock Availability'],
            ['index' => 'action'],
        ];
    }

    #[Computed(cache: true)]
    public function rows()
    {
        return Stock::query()->with('supply:id,name')
            ->when($this->search, function($query) {
                $query->whereHas('supply', function(Builder $supplyQuery) {
                    return $supplyQuery->where('name', 'like', "{$this->search}%");
                });
            })
            ->paginate($this->quantity)
            ->withQueryString();
    }

    public function create() {
        dd($this->requestedQuantity);
    }


    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.requisition-table');
    }
}
