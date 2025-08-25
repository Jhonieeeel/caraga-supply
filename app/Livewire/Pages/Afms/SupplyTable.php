<?php

namespace App\Livewire\Pages\Afms;

use App\Actions\Supply\CreateSupplyAction;
use App\Actions\Supply\EditSupplyAction;
use App\Livewire\Forms\SupplyForm;
use App\Models\Supply;
use Illuminate\Contracts\Database\Query\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\WithPagination;

#[Lazy()]
class SupplyTable extends Component
{

    use WithPagination;

    // table
    public ?string $search = null;

    public ?int $quantity = 5;
    public $headers = [];

    // form
    public SupplyForm $supplyForm;
    public ?Supply $supply;

    public function mount( )
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

    #[Computed()]
    public function rows()
    {
        return Supply::query()
            ->when($this->search, function(Builder $query) {
                return $query->where('name', 'like', "{$this->search}%");
            })
            ->paginate($this->quantity)
            ->withQueryString();
    }

    // crud
    // create
    public function create(CreateSupplyAction $create_supply_action) {
        $this->supplyForm->create($create_supply_action);
        $this->dispatch('modal:add-close');
    }

    // edit and update
    public function edit(Supply $supply) {
        $this->supply = $supply;
        $this->supplyForm->fillForm($supply);
        $this->dispatch('modal:edit-open');
    }

    public function update(EditSupplyAction $edit_supply_action) {
        $this->supplyForm->update($this->supply, $edit_supply_action);
        $this->dispatch('modal:edit-close');
    }

    // delete
    public function delete($id) {
        $supply = Supply::findOrFail($id);
        if ($supply->stocks()->exists()) {
            return session()->flash('message', [
            'text' => 'Cannot delete this supply because it has related stocks.',
            'color' => 'red',
            'title' => 'Error',
        ]);
        }

        return $supply->delete();
            session()->flash('message', [
            'text' => 'Supply deleted successfully.',
            'color' => 'green',
            'title' => 'Success',
        ]);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.supply-table');
    }
}
