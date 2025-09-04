<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Actions\Requisition\UpdateRequestAction;
use App\Actions\RequisitionItem\UpdateItemAction;
use App\Actions\Stock\UpdateStockQuantity;
use App\Livewire\Forms\ItemForm;
use App\Livewire\Forms\RequisitionForm;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class RequestDetail extends Component
{
    public Requisition $requisition;
    public RequisitionItem $requisitionItem;

    public RequisitionForm $requestForm;
    public ItemForm $itemForm;


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

    public function update(UpdateRequestAction $update_request_action, UpdateStockQuantity $update_stock_quantity)
    {
        $response = $this->requestForm->update($this->requisition, $update_request_action, $update_stock_quantity);

        if ($response->completed) {
            $update_stock_quantity->handle($response);
        }

        $this->requisition = $response;
        $this->dispatch('current-data', id: $this->requisition->id);

        return $this->requisition;
    }

    #[On('view-requisition')]
    public function view($id)
    {
        $this->requisition = Requisition::find($id)->load('items.stock.supply');
        $this->requestForm->fillForm($this->requisition);
    }

    public function render()
    {
        return view('livewire.pages.afms.components.request-detail');
    }
}
