<?php

namespace App\Livewire\Pages\Afms;

use App\Models\Supply;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;

class SupplyTable extends Component
{

    use WithPagination;

    // table
    public ?string $search = '';
    public ?int $quantity = 5;
    public $headers = [];

    public function mount($id = null)
    {
        $this->headers = [
            ['index' => 'name', 'label' => 'Supply Name'],
            ['index' => 'category', 'label' => 'Category'],
            ['index' => 'unit', 'label' => 'Unit'],
            ['index' => 'action'],
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function getRowsProperty()
    {
        return Supply::query()
            ->when($this->search, function (Builder $query) {
                return $query->where('name', 'like', "%{$this->search}%");
            })
            ->paginate($this->quantity)
            ->withQueryString();
    }


    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.supply-table', ['rows' => $this->getRowsProperty()]);
    }
}
