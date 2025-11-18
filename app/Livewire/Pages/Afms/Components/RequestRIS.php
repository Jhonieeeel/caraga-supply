<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Actions\Requisition\UpdateRequestAction;
use App\Actions\Stock\UpdateStockQuantity;
use App\Actions\Transaction\CreateTransaction;
use App\Jobs\ProcessRequisition;
use App\Livewire\Forms\RequisitionForm;
use App\Livewire\Pages\Afms\RequisitionTable;
use App\Models\Requisition;
use Illuminate\Support\Facades\Log;
use Imtigger\LaravelJobStatus\TrackableJob;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class RequestRIS extends Component
{
    use WithFileUploads;

    public Requisition $requisition;
    public ?RequisitionForm $requestForm;

    public $pdfStatus;

    public $temporaryFile;
    public $step = 1;

    public $tab = 'RIS';


    // RIS
    public function updateRIS(UpdateRequestAction $edit_request_action, UpdateStockQuantity $update_stock_quantity, CreateTransaction $create_transaction)
    {
        $this->requestForm->temporaryFile = $this->temporaryFile;
        $this->requestForm->fillForm($this->requisition);
        $this->requisition = $this->requestForm->update($this->requisition, $edit_request_action, $update_stock_quantity, $create_transaction);

        $this->dispatch('alert', [
            'text' => 'Requisition Updated Successfully.',
            'color' => 'teal',
            'title' => 'Success'
        ]);

        $this->dispatch('change-tab', tab: 'List')->to(RequisitionTable::class);
        $this->dispatch('update-list', id: $this->requisition->id)->to(RequestTable::class);

        return;
    }

    // generate ris
    public function getRIS()
    {
        // generate pdf
        ProcessRequisition::dispatchSync($this->requisition->id);

        $this->dispatch('update-list', id: $this->requisition->id);

        return $this->dispatch('alert', [
            'text' => 'Requisition Generated successfully.',
            'color' => 'teal',
            'title' => 'Requisition and Issuance Slip'
        ]);
    }

    #[On('update-list')]
    public function updateList($id = null) {}

    #[On('current-data')]
    public function currentData($requisition)
    {
        $this->step = 1;
        $this->requisition = Requisition::with('items.stock.supply')->find($requisition);
    }

    #[On('refresh')]
    public function render()
    {
        return view('livewire.pages.afms.components.request-r-i-s');
    }
}
