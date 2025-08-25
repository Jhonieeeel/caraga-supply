<?php

namespace App\Livewire\Forms;

use App\Actions\Requisition\CreateRequestAction;
use App\Models\Requisition;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Form;

class RequisitionForm extends Form
{
    #[Rule(['required', 'nullable'])]
    public $ris;

    #[Rule(['required', 'exists:users,id'])]
    public $user_id;

    #[Rule(['nullable', 'exists:users,id'])]
    public $requested_by;

    #[Rule(['nullable', 'exists:users,id'])]
    public $issued_by;

    #[Rule(['nullable', 'exists:users,id'])]
    public $approved_by;

    #[Rule(['nullable', 'exists:users,id'])]
    public $received_by;

    public function create(CreateRequestAction $create_request_action) {
        return $create_request_action->handle($this->toArray());
    }

    public function toArray(): array {
        return [
            'ris' => $this->ris,
            'user_id' => Auth::id(),
            'requested_by' => $this->requested_by,
            'approved_by' => $this->approved_by,
            'issued_by' => $this->issued_by,
            'received_by' => $this->received_by,
        ];
    }

    public function fillForm(Requisition $requisition): void {
        $this->ris = $requisition->ris;
        $this->user_id = $requisition->user_id;
        $this->requested_by = $requisition->requested_by;
        $this->approved_by = $requisition->approved_by;
        $this->issued_by = $requisition->issued_by;
        $this->received_by = $requisition->received_by;
    }
}
