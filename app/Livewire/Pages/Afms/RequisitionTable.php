<?php

namespace App\Livewire\Pages\Afms;

use App\Actions\Requisition\CreateRequestAction;
use App\Actions\Requisition\UpdateRequestAction;
use App\Actions\RequisitionItem\CreateItemAction;
use App\Livewire\Forms\ItemForm;
use App\Livewire\Forms\RequisitionForm;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Contracts\Database\Query\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class RequisitionTable extends Component
{
    use WithPagination;

    // for modal table
    public $headers = [];
    public $search = '';
    public $quantity = 5;



    // for request headers
    public $requestHeaders = [];
    public $requestSearch = '';

    // Tab
    public $tab = 'Requests List';
    public $step = 1;

    public ?Requisition $requisition;

    // form
    public RequisitionForm $requestForm;
    public ItemForm $itemForm;

    public function mount()
    {
        // for modal table
        $this->headers = [
            ['index' => 'supply.name', 'label' => 'Supply name'],
            ['index' => 'quantity', 'label' => 'Stock Availability'],
            ['index' => 'action'],
        ];

        $this->requestHeaders = [
            ['index' => 'user.name', 'label' => 'Requested By'],
            ['index' => 'items_count', 'label' => 'Total Requested Items'],
            ['index' => 'action']
        ];
    }

    public function deleteRequisition($requisitionId)
    {
        $this->requisition = Requisition::find($requisitionId);
        $this->requisition->delete();

        return session()->flash('message', [
            'text' => 'Requisition deleted successfully.',
            'color' => 'red',
            'title' => 'Deleted'
        ]);
    }

    public function viewRequisition(Requisition $requisition)
    {
        $this->requisition = $requisition->load('items.stock.supply');
        $this->requestForm->fillForm($this->requisition);
        $this->tab = "Requests Detail";
    }

    #[Computed()]
    public function getUsers()
    {
        return User::all(['id', 'name'])
            ->map(fn($user) => [
                'label' => $user->name,
                'value' => $user->id,
            ]);
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

    #[Computed()]
    public function requestRows()
    {
        return Requisition::query()->with(['user:id,name', 'items'])->withCount('items')
            ->when($this->requestSearch, function ($query) {
                $query->whereHas('user', function (Builder $requestQuery) {
                    return $requestQuery->where('name', 'like', "{$this->requestSearch}%");
                });
            })
            ->paginate($this->quantity)
            ->withQueryString();
    }

    public function create(CreateRequestAction $create_request_action, CreateItemAction $create_item_action)
    {
        $this->requisition = $this->requestForm->create($create_request_action);
        $this->itemForm->create($create_item_action, $this->requisition);
        $this->dispatch('modal:add-request-close');
        return session()->flash('message', [
            'text' => 'Requisition added successfully.',
            'color' => 'green',
            'title' => 'Success'
        ]);
    }

    public function update(UpdateRequestAction $update_request_action)
    {
        $data = $this->requestForm->update($this->requisition, $update_request_action);
        dd($data);
    }

    public function generateRIS()
    {
        $date = now()->format('m-d-Y');
        $count = Requisition::count();
        return $this->requestForm->ris = "RIS_{$date}_{$count}";
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.requisition-table');
    }
}
