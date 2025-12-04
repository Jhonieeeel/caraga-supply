<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Actions\Requisition\UpdateRequestAction;
use App\Actions\RequisitionItem\UpdateItemAction;
use App\Actions\Stock\UpdateStockQuantity;
use App\Actions\Transaction\CreateTransaction;
use App\Livewire\Forms\ItemForm;
use App\Livewire\Forms\RequisitionForm;
use App\Livewire\Pages\Afms\RequisitionTable;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

class RequestDetail extends Component
{
    use Interactions;
    public $requisition;
    public RequisitionItem $requisitionItem;

    public RequisitionForm $requestForm;
    public ItemForm $itemForm;

    public function viewPdf()  {
        $this->dispatch('change-tab', tab: 'RIS')->to(RequisitionTable::class);
        $this->dispatch('change-ris-step', step: 2);
    }

    public function approvedRequisition(Requisition $requisition)
    {
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
        $this->requisitionItem = $item;
        $this->itemForm->fillForm($this->requisitionItem);
        $this->dispatch('modal:edit-item-open');
        return;
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
        return;
    }

    #[On('generate-ris')]
    public function generateRIS()
    {
        $date = now()->format('Y-m');
        $count = Requisition::count();
        return $this->requestForm->ris = "RIS-{$date}-{$count}";
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

    public function update(UpdateRequestAction $update_request_action, UpdateStockQuantity $update_stock_quantity, CreateTransaction $create_transaction)
    {

        $response = $this->requestForm->update($this->requisition, $update_request_action, $update_stock_quantity, $create_transaction);
        if ($response->completed) {
            $update_stock_quantity->handle($response);
        }

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

        $isApproved = $this->requisition->requested_by && $this->requisition->approved_by && $this->requisition->issued_by && $this->requisition->received_by;

        if (!$isApproved) {
            $this->dispatch('generate-ris');
        }

        return $this->requisition;
    }

    public function render()
    {
        return view('livewire.pages.afms.components.request-detail');
    }
}
