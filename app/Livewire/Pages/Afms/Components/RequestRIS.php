<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Actions\Requisition\UpdateRequestAction;
use App\Actions\Stock\UpdateStockQuantity;
use App\Jobs\ProcessRequisition;
use App\Livewire\Forms\RequisitionForm;
use App\Models\Requisition;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class RequestRIS extends Component
{
    use WithFileUploads;

    public ?Requisition $requisition;
    public ?RequisitionForm $requestForm;

    public $temporaryFile;
    public $step = 1;

    // RIS
    public function updateRIS(UpdateRequestAction $edit_request_action, UpdateStockQuantity $update_stock_quantity)
    {
        $this->requestForm->temporaryFile = $this->temporaryFile;
        $this->requisition = $this->requestForm->update($this->requisition, $edit_request_action, $update_stock_quantity);

        session()->flash('message', [
            'text' => 'Requisition Updated Successfully.',
            'color' => 'teal',
            'title' => 'Success'
        ]);

        return redirect(route('requisition.index'));
    }

    // generate ris
    public function getRIS()
    {
        ProcessRequisition::dispatch($this->requisition->id);

        $this->dispatch('alert', [
            'text' => 'Requisition Generated successfully.',
            'color' => 'teal',
            'title' => 'Requisition and Issuance Slip'
        ]);

        $this->dispatch('refresh');

        return $this->requisition;
    }



    #[On('current-data')]
    public function currentData($id): void
    {
        $this->requisition = Requisition::find($id);
    }

    #[On('refresh')]
    public function render()
    {
        return view('livewire.pages.afms.components.request-r-i-s');
    }
}
