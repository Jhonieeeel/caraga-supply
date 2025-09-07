<?php

namespace App\Livewire\Pages\Afms;

use App\Actions\Requisition\CreateRequestAction;
use App\Actions\RequisitionItem\CreateItemAction;
use App\Livewire\Forms\ItemForm;
use App\Livewire\Forms\RequisitionForm;
use App\Livewire\Pages\Afms\Components\RequestTable;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Stock;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class RequisitionTable extends Component
{
    use WithPagination, WithFileUploads;

    // for modal table
    public $headers = [];
    public $search = '';
    public $quantity = 5;

    // Tab
    public $tab = 'List';

    public ?Requisition $requisition;
    public ?RequisitionItem $requisitionItem;

    // form
    public ?RequisitionForm $requestForm;
    public ?ItemForm $itemForm;

    public $session = [];

    public function mount()
    {
        // for modal table
        $this->headers = [
            ['index' => 'stock_number', 'label' => 'Stock ID'],
            ['index' => 'supply.name', 'label' => 'Supply name'],
            ['index' => 'quantity', 'label' => 'Stock Availability'],
            ['index' => 'action'],
        ];
    }

    // for modal table
    #[Computed()]
    public function rows()
    {
        return Stock::query()->with('supply:id,name')
            ->when($this->search, function ($query) {
                $query->whereHas('supply', function (Builder $supplyQuery) {
                    return $supplyQuery->where('name', 'like', "{$this->search}%");
                });
            })
            ->paginate($this->quantity)
            ->withQueryString();
    }

    public function create(CreateRequestAction $create_request_action, CreateItemAction $create_item_action)
    {
        if (!count($this->itemForm->requestedItems)) {
            throw ValidationException::withMessages([
                'itemForm.requestedItems' => 'Please add at least one item.',
            ]);
        }

        $newRequisition = $this->requestForm->create($create_request_action);
        $this->itemForm->create($create_item_action, $newRequisition);

        $this->dispatch('update-list');
        $this->dispatch('modal:add-request-close');

        return;
    }


    #[On('change-tab')]
    public function changeTab($tab)
    {
        $this->tab = $tab;
    }

    #[On('alert')]
    public function alert($session)
    {
        return session()->flash('message', $session);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.requisition-table');
    }
}
