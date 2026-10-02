<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Actions\Requisition\UpdateRequestAction;
use App\Actions\RequisitionItem\RemoveItemAction;
use App\Actions\RequisitionItem\UpdateItemAction;
use App\Domain\Gasu\Exceptions\InsufficientStockException;
use App\Domain\Gasu\Exceptions\RequisitionLockedException;
use App\Livewire\Forms\ItemForm;
use App\Livewire\Forms\RequisitionForm;
use App\Livewire\Pages\Afms\RequisitionTable;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

class RequestDetail extends Component
{
    use Interactions;
    public $requisition;
    public ?RequisitionItem $requisitionItem = null;

    public RequisitionForm $requestForm;
    public ItemForm $itemForm;

    public function goBack()
    {
        $this->dispatch('change-tab', tab: 'List')->to(RequisitionTable::class);
    }

    public function viewPdf()  {
        $this->dispatch('change-tab', tab: 'RIS')->to(RequisitionTable::class);
        $this->dispatch('change-ris-step', step: 2);
    }

    public function approvedRequisition(Requisition $requisition)
    {
        $this->authorize('approve', $requisition);

        $requisition->status = 'approved';
        $requisition->save();
        $this->dispatch('alert', [
            'text' => 'Requisition Approved.',
            'color' => 'teal',
            'title' => 'Requisition and Issuance Slip'
        ]);
        $this->dispatch('change-tab', tab: 'RIS')->to(RequisitionTable::class);
        $this->dispatch('current-data', requisition: $requisition->id);

        return;
    }

    public function editRequestItem(RequisitionItem $item)
    {
        $this->authorize('update', $item->requisition);

        $this->requisitionItem = $item;
        $this->itemForm->fillForm($this->requisitionItem);
        $this->dispatch('modal:edit-item-open');
        return;
    }

    public function updateRequestItem(UpdateItemAction $update_item_action)
    {
        abort_unless($this->requisitionItem, 404);
        $this->authorize('update', $this->requisitionItem->requisition);

        try {
            // Quantity changes go through the aggregates (allocate more /
            // release back to the lot), never a direct requisition_items update.
            $this->itemForm->update($update_item_action, $this->requisitionItem);
        } catch (InsufficientStockException|RequisitionLockedException|\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'itemForm.requested_qty' => $e instanceof InsufficientStockException
                    ? 'Not enough stock available for that quantity.'
                    : $e->getMessage(),
            ]);
        }

        $this->dispatch('modal:edit-item-close');
        $this->refreshRequisition();
    }

    public function deleteRequisitionItem($id, RemoveItemAction $remove_item_action)
    {
        $item = RequisitionItem::findOrFail($id);

        $this->authorize('update', $item->requisition);

        try {
            // Releases the item's allocated stock back to its lot.
            $remove_item_action->handle($item);
        } catch (RequisitionLockedException|\InvalidArgumentException $e) {
            $this->dialog()->error('Error', $e->getMessage())->send();

            return;
        }

        $this->refreshRequisition();
    }

    protected function refreshRequisition(): void
    {
        if ($this->requisition) {
            $this->requisition = Requisition::with('items.stock.supply')->find($this->requisition->id);
        }
    }

    #[On('generate-ris')]
    public function generateRIS()
    {
        $prefix = 'RIS-' . now()->format('Y-m') . '-';

        // Next series = highest existing series for this year-month + 1
        // (a count would reuse a number after deletions and collide with the
        // unique requisitions.ris index).
        $maxSeries = Requisition::where('ris', 'like', "{$prefix}%")
            ->pluck('ris')
            ->map(fn (string $ris) => (int) substr($ris, strlen($prefix)))
            ->max() ?? 0;

        $series = str_pad($maxSeries + 1, 3, '0', STR_PAD_LEFT);

        return $this->requestForm->ris = "{$prefix}{$series}";
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

    public function update(UpdateRequestAction $update_request_action)
    {
        $this->authorize('update', $this->requisition);

        $response = $this->requestForm->update($this->requisition, $update_request_action);

        $this->requisition = $response;

        $this->dialog()->success('Success', 'Request Detail Updated!')->send();

        return $this->redirectRoute('requisition.index');
    }

    #[On('update-detail-state')]
    public function updateList($id = null) {

    }

    #[On('view-requisition')]
    public function view($requisition)
    {

        if (!$requisition) {
            $this->requisition = null;
            return;
        }

        $this->requisition = Requisition::with('items.stock.supply')->find($requisition);
        $this->requestForm->fillForm($this->requisition);

        // Only number a requisition that has no RIS yet; never renumber one
        // that already has its RIS.
        if (! $this->requisition->ris) {
            $this->dispatch('generate-ris');
        }

        return $this->requisition;
    }

    public function render()
    {
        return view('livewire.pages.afms.components.request-detail');
    }
}
