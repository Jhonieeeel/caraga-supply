<?php

namespace App\Livewire\Forms;

use App\Actions\Requisition\CreateRequestAction;
use App\Actions\Requisition\UpdateRequestAction;
use App\Actions\Stock\UpdateStockQuantity;
use App\Actions\Transaction\CreateTransaction;
use App\Models\Requisition;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Illuminate\Support\Str;

class RequisitionForm extends Form
{
    #[Rule(['unique:requisitions,ris', 'nullable', 'min:6'])]
    public $ris;

    #[Rule('exists:users,id')]
    public $user_id;

    #[Rule(['nullable', 'exists:users,id'])]
    public $requested_by;

    #[Rule(['nullable', 'exists:users,id'])]
    public $issued_by;

    #[Rule(['nullable', 'exists:users,id'])]
    public $approved_by;

    #[Rule(['nullable', 'exists:users,id'])]
    public $received_by;

    #[Rule('nullable')]
    public $completed;

    #[Rule('nullable')]
    public $status;

    #[Validate(['nullable', 'file', 'mimes:pdf'])]
    public $pdf;

    public $temporaryFile;

    public function create(CreateRequestAction $create_request_action)
    {
        $this->validate();

        $requisition = Requisition::where('user_id', Auth::id())->where('completed', false)->first();

        if (!$requisition) {
            return $create_request_action->handle($this->newArray());
        }

        $this->reset();

        return $requisition;
    }

    public function update(Requisition $requisition, UpdateRequestAction $edit_request_action, UpdateStockQuantity $update_stock_quantity, CreateTransaction $create_transaction)
    {
        if (!$this->ris) {
            $this->validate();
        }

        $currentPath = storage_path('app/public/' . $requisition->pdf);

        if ($this->temporaryFile && file_exists($currentPath)) {
            unlink($currentPath);
            $extension = $this->temporaryFile->getClientOriginalExtension();

            $date = now()->format('Ymd');
            $userId = $requisition->user_id;
            $random = substr((string) Str::uuid(), 0, 8);

            $uniqueName = "SIGNED_RIS_{$date}_{$userId}_{$random}.{$extension}";

            $storedPath = $this->temporaryFile->storeAs(
                'ris',
                $uniqueName,
                'public'
            );

            $this->pdf = $storedPath;
            $this->status = 'completed';
            $this->completed = true;
            $update_stock_quantity->handle($requisition);
        }

        $edit_request_action->handle($requisition, $this->toArray());

        $requisition->refresh();

        if ($requisition->completed) {
            foreach ($requisition->items as $item) {
                $create_transaction->handle([
                    'requisition_id' => $requisition->id,
                    'stock_id' => $item->stock_id,
                    'quantity' => $item->requested_qty,
                    'type_of_transaction' => 'RIS',
                ]);
            }
        }

        return $requisition;
    }

    public function toArray(): array
    {
        return [
            'ris' => $this->ris,
            'user_id' => Auth::id(),
            'requested_by' => $this->requested_by,
            'approved_by' => $this->approved_by,
            'issued_by' => $this->issued_by,
            'received_by' => $this->received_by,
            'pdf' => $this->pdf, // Use the renamed/stored file path
            'completed' => $this->completed ?? false
        ];
    }

    public function newArray(): array
    {
        return [
            'ris' => null,
            'user_id' => Auth::id(),
            'requested_by' => Auth::id(),
            'approved_by' => null,
            'issued_by' => null,
            'received_by' => null,
            'pdf' => null,
            'completed' => false
        ];
    }

    public function fillForm(Requisition $requisition): void
    {
        $this->ris = $requisition->ris;
        $this->user_id = $requisition->user_id;
        $this->requested_by = $requisition->requested_by;
        $this->approved_by = $requisition->approved_by;
        $this->issued_by = $requisition->issued_by;
        $this->received_by = $requisition->received_by;
        $this->completed = $requisition->completed;
    }
}
