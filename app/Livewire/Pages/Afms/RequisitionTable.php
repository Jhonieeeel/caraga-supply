<?php

namespace App\Livewire\Pages\Afms;

use App\Actions\Requisition\CreateRequestAction;
use App\Actions\Requisition\UpdateRequestAction;
use App\Actions\RequisitionItem\CreateItemAction;
use App\Actions\RequisitionItem\UpdateItemAction;
use App\Actions\Stock\UpdateStockQuantity;
use App\Livewire\Forms\ItemForm;
use App\Livewire\Forms\RequisitionForm;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Stock;
use App\Models\User;
use App\Services\Afms\ConvertRisService;
use App\Services\Afms\GenerateRisService;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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



    // for request headers
    public $requestHeaders = [];
    public $requestSearch = '';


    // Tab
    public $tab = 'Requests List';
    public $step = 1;

    public ?Requisition $requisition;
    public ?RequisitionItem $requisitionItem;

    // form
    public ?RequisitionForm $requestForm;
    public ?ItemForm $itemForm;

    // temp
    public $temporaryFile;

    public $rsmiDate;
    public $rsmiSearch;
    public $rsmi;


    public function mount()
    {
        // for modal table
        $this->headers = [
            ['index' => 'stock_number', 'label' => 'Stock ID'],
            ['index' => 'supply.name', 'label' => 'Supply name'],
            ['index' => 'quantity', 'label' => 'Stock Availability'],
            ['index' => 'action'],
        ];

        $this->requestHeaders = [
            ['index' => 'user.name', 'label' => 'Requested By'],
            ['index' => 'items_count', 'label' => 'Total Requested Items'],
            ['index' => 'completed', 'label' => 'Status'],
            ['index' => 'action']
        ];
    }

    // RSMI

    #[Computed()]
    public function getSupplies()
    {
        return RequisitionItem::with('requisition')
            ->whereHas('requisition', function ($query) {
                $query->whereCompleted('true');
            })
            ->distinct('stock_id')
            ->get()
            ->load('stock.supply')
            ->map(fn($item) => [
                'label' => $item->stock->supply->name,
                'value' => $item->stock_id,
                'note' => $item->stock->stock_number
            ]);
    }

    public function createRsmi()
    {

        $end = Carbon::parse($this->rsmiDate[1])->addDay();

        $this->rsmi = Requisition::with('items.stock')
            ->where('completed', true)
            ->whereBetween('created_at', [$this->rsmiDate[0], $end])
            ->whereHas('items', function ($query) {
                $query->where('stock_id', $this->rsmiSearch);
            })
            ->get();

        dd($this->rsmi);

        return $this->rsmi;
    }


    #[Computed()]
    public function getRSMI()
    {
        return RequisitionItem::with('requisition')
            ->whereHas('requisition', function ($query) {
                $query->whereCompleted('true');
            })
            ->distinct('stock_id')
            ->get()
            ->load('stock');
    }

    public function editRequestItem(RequisitionItem $item)
    {
        $this->requisitionItem = $item;
        $this->itemForm->fillForm($this->requisitionItem);
        $this->dispatch('modal:edit-item-open');
    }

    public function updateRequestItem(UpdateItemAction $update_item_action)
    {
        $this->dispatch('modal:edit-item-close');
        return $this->itemForm->update($update_item_action, $this->requisitionItem);
    }

    public function deleteRequisitionItem($id)
    {
        $item = RequisitionItem::find($id);
        $item->delete();
    }

    // RIS
    public function updateRIS(UpdateRequestAction $edit_request_action)
    {
        $this->requestForm->temporaryFile = $this->temporaryFile;
        $this->requisition = $this->requestForm->update($this->requisition, $edit_request_action);

        session()->flash('message', [
            'text' => 'Requisition Updated Successfully.',
            'color' => 'teal',
            'title' => 'Success'
        ]);

        return redirect(route('requisition.index'));
    }

    // generate ris
    public function getRIS(GenerateRisService $generate_ris_service, ConvertRisService $convert_ris_service)
    {
        $requisitionDocx = $generate_ris_service->handle($this->requisition);
        $requisition = $convert_ris_service->handle($requisitionDocx, $this->requisition);

        $this->requisition = $requisition;

        return session()->flash('message', [
            'text' => 'Requisition Generated successfully.',
            'color' => 'teal',
            'title' => 'Requisition and Issuance Slip'
        ]);
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

    #[On('refresh')]
    #[Computed()]
    public function requestRows()
    {
        return Requisition::query()
            ->with(['user', 'items'])
            ->withCount('items')
            ->when($this->search, function (Builder $query) {
                $query->whereHas('user', function ($user) {
                    $user->where('name', 'like', "%{$this->search}%");
                });
            })
            ->latest()
            ->paginate($this->quantity)
            ->withQueryString();
    }

    public function create(CreateRequestAction $create_request_action, CreateItemAction $create_item_action)
    {
        $newRequisition = $this->requestForm->create($create_request_action);
        // $this->requestForm->fillForm($newRequisition);
        $this->itemForm->create($create_item_action, $newRequisition);
        $this->requestForm->reset();
        $this->itemForm->reset();
        $this->dispatch('modal:add-request-close');

        return session()->flash('message', [
            'text' => 'Requisition added successfully.',
            'color' => 'green',
            'title' => 'Success'
        ]);
    }

    public function update(UpdateRequestAction $update_request_action, UpdateStockQuantity $update_stock_quantity)
    {
        $response = $this->requestForm->update($this->requisition, $update_request_action);

        if ($response->completed) {
            $update_stock_quantity->handle($response);
        }

        $this->requisition = $response;

        return;
    }

    public function generateRIS()
    {
        $date = now()->format('m-d-Y');
        $count = Requisition::count();
        $current_user = Auth::id();
        return $this->requestForm->ris = "RIS_{$current_user}_{$date}_{$count}";
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.requisition-table');
    }
}
