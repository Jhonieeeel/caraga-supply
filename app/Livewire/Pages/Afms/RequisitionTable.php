<?php

namespace App\Livewire\Pages\Afms;

use App\Actions\Requisition\CreateRequestAction;
use App\Actions\Requisition\DeleteRequisitionAction;
use App\Actions\RequisitionItem\AllocateStockToRequisitionAction;
use App\Domain\Gasu\Exceptions\InsufficientStockException;
use App\Domain\Gasu\Exceptions\RequisitionLockedException;
use App\Events\RequestCreated;
use App\Livewire\Forms\ItemForm;
use App\Livewire\Forms\RequisitionForm;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Stock;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;

class RequisitionTable extends Component
{
    use WithPagination, WithFileUploads, Interactions;

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

    public $loggedInId;

    public function mount()
    {
        // for modal table
        $this->headers = [
            ['index' => 'stock_number', 'label' => 'Stock ID'],
            ['index' => 'supply.name', 'label' => 'Supply name'],
            ['index' => 'quantity', 'label' => 'Stock Availability'],
            ['index' => 'action'],
        ];

        $this->loggedInId = Auth::id();


    }

    #[Computed()]
    public function rows()
    {
        return Stock::query()
            ->with('supply:id,name')
            ->when($this->search, function ($query) {
                $query->whereHas('supply', function (Builder $supplyQuery) {
                    $supplyQuery->where('name', 'like', "%{$this->search}%");
                });
            })
            ->paginate($this->quantity)
            ->withQueryString();
    }

   public function create(CreateRequestAction $create_request_action, AllocateStockToRequisitionAction $allocate_stock_action, DeleteRequisitionAction $delete_requisition_action)
    {
        $this->authorize('create', Requisition::class);

        // Validate every line (whole number >= 1, within available stock)
        // before anything is created or allocated.
        $items = $this->itemForm->validatedItems();

        $existingIds = Requisition::where('user_id', Auth::id())->where('completed', false)->pluck('id');

        $newRequisition = $this->requestForm->create($create_request_action);


        // mo add og items if the RIS is still false or not completed
        if ($newRequisition) {

            try {
                // All-or-nothing: if any line can't be allocated (e.g. stock
                // was taken meanwhile), no line is.
                $this->itemForm->create($allocate_stock_action, $newRequisition, $items);
            } catch (InsufficientStockException|RequisitionLockedException|\InvalidArgumentException $e) {
                // Don't leave a freshly opened, empty requisition behind.
                if (! $existingIds->contains($newRequisition->id) && ! $newRequisition->items()->exists()) {
                    $delete_requisition_action->handle($newRequisition);
                }

                throw ValidationException::withMessages([
                    'itemForm.requestedItems' => $e instanceof InsufficientStockException
                        ? 'Not enough stock is available for one of the requested items. Please adjust the quantities.'
                        : $e->getMessage(),
                ]);
            }

            broadcast(new RequestCreated($newRequisition))->toOthers();
            $this->dialog()->success('Success', 'Request Added!')->flash()->send();
            $this->dispatch('modal:add-request-close');

            $this->requestForm->reset();
            $this->itemForm->reset();
        }


        return $this->redirectRoute('requisition.index');
    }

    /**
     * Clicking the "List" tab: reset the stock picker's search/pagination so
     * the list starts fresh.
     */
    public function refreshStates()
    {
        $this->reset('search');
        $this->resetPage();
        unset($this->rows);
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
