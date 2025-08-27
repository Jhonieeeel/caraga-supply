<?php

namespace App\Livewire\Forms;

use App\Actions\Requisition\CreateRequestAction;
use App\Actions\Requisition\UpdateRequestAction;
use App\Models\Requisition;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class RequisitionForm extends Form
{
    #[Rule(['nullable', 'min:6'])]
    public $ris;

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

    #[Validate(['nullable', 'file', 'mimes:pdf'])]
    public $pdf;

    public $temporaryFile;

    public function create(CreateRequestAction $create_request_action)
    {
        $this->validate();
        $requisition = Requisition::where('user_id', Auth::id())->where('completed', false)->first();
        if (!$requisition) {
            return $create_request_action->handle($this->toArray());
        }

        return $requisition;
    }

    public function update(Requisition $requisition, UpdateRequestAction $edit_request_action)
    {
        $this->validate();

        $currentPath = storage_path('app/public/' . $requisition->pdf);

        if ($this->temporaryFile && file_exists($currentPath)) {
            unlink($currentPath);

            $storedPath = $this->temporaryFile->storeAs(
                'ris',
                $this->temporaryFile->getClientOriginalName(),
                'public'
            );

            $this->pdf = $storedPath;
            $this->completed = true;
        }

        return $edit_request_action->handle($requisition, $this->toArray());
    }

    public function toArray(): array
    {
        $uploadedPdf = $this->temporaryFile
            ? 'ris/' . $this->temporaryFile->getClientOriginalName()
            : $this->pdf;

        return [
            'ris' => $this->ris,
            'user_id' => Auth::id(),
            'requested_by' => Auth::id(),
            'approved_by' => $this->approved_by,
            'issued_by' => $this->issued_by,
            'received_by' => $this->received_by,
            'pdf' => $uploadedPdf,
            'completed' => $this->completed
        ];
    }

    public function fillForm(Requisition $requisition): void
    {
        $this->ris = $requisition->ris;
        $this->requested_by = $requisition->requested_by;
        $this->approved_by = $requisition->approved_by;
        $this->issued_by = $requisition->issued_by;
        $this->received_by = $requisition->received_by;
    }
}
