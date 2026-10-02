<?php

namespace App\Livewire\Forms;

use App\Actions\Requisition\CreateRequestAction;
use App\Actions\Requisition\UpdateRequestAction;
use App\Domain\Gasu\Aggregates\RequisitionAggregate;
use App\Models\Requisition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule as ValidationRule;
use Illuminate\Validation\ValidationException;
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

    #[Rule(['nullable', 'string', 'max:255'])]
    public $purpose;

    public $pdf;

    // Signed RIS upload: PDF only. It is always stored with a .pdf extension
    // (never the client's), see update().
    #[Validate(['nullable', 'file', 'mimes:pdf', 'max:10240'])]
    public $temporaryFile;

    // dates

    #[Rule(['nullable', 'date', 'after_or_equal:requested_date'])]
    public $approved_date;

    #[Rule(['nullable', 'date'])]
    public $requested_date;

    #[Rule(['nullable', 'date', 'after_or_equal:requested_date'])]
    public $issued_date;

    #[Rule(['nullable', 'date', 'after_or_equal:requested_date'])]
    public $received_date;

    protected function messages(): array
    {
        return [
            'approved_date.after_or_equal' => 'The approved date cannot be earlier than the requested date.',
            'issued_date.after_or_equal' => 'The issued date cannot be earlier than the requested date.',
            'received_date.after_or_equal' => 'The received date cannot be earlier than the requested date.',
        ];
    }

    public function create(CreateRequestAction $create_request_action)
    {
        $this->user_id = Auth::id();

        $requisition = Requisition::where('user_id', Auth::id())->where('completed', false)->first();

        $this->validate();

        if (!$requisition) {
            return $create_request_action->handle($this->newArray());
        }

        $this->reset();

        return $requisition;
    }

    public function update(Requisition $requisition, UpdateRequestAction $edit_request_action)
    {
        // Always the full rule set (the RIS being filled in no longer skips
        // the unique/exists checks), with the RIS uniqueness ignoring this
        // requisition's own current value.
        $this->validate($this->updateRules($requisition));

        $this->guardApprovalFields($requisition);

        if ($this->temporaryFile) {
            // Only delete an existing copy — on a requisition's first-ever
            // upload $requisition->pdf is still null, so there is nothing to
            // delete.
            if ($requisition->pdf) {
                Storage::disk('public')->delete($requisition->pdf);
            }

            $date = now()->format('Ymd');
            $userId = $requisition->user_id;
            $random = substr((string) Str::uuid(), 0, 8);

            // Validated as a PDF above; never trust/keep the client's
            // extension on the public disk.
            $uniqueName = "SIGNED_RIS_{$date}_{$userId}_{$random}.pdf";

            $storedPath = $this->temporaryFile->storeAs(
                'ris',
                $uniqueName,
                'public'
            );

            $this->pdf = $storedPath;
            $this->status = 'completed';
            $this->completed = true;

            // Guards internally against re-completing an already-completed
            // requisition — this is a structural no-op in that case, rather
            // than a flag we have to remember to check, which is what
            // permanently closes the duplicate-transaction-logging bug class.
            RequisitionAggregate::retrieve($requisition->uuid)
                ->complete($storedPath, Auth::id())
                ->persist();
        }

        $edit_request_action->handle($requisition, $this->toArray());

        return $requisition->fresh();
    }

    protected function updateRules(Requisition $requisition): array
    {
        return [
            'ris' => ['nullable', 'string', 'min:6', 'max:255', ValidationRule::unique('requisitions', 'ris')->ignore($requisition->id)],
            'requested_by' => ['nullable', 'exists:users,id'],
            'issued_by' => ['nullable', 'exists:users,id'],
            'approved_by' => ['nullable', 'exists:users,id'],
            'received_by' => ['nullable', 'exists:users,id'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'requested_date' => ['nullable', 'date'],
            'approved_date' => ['nullable', 'date', 'after_or_equal:requested_date'],
            'issued_date' => ['nullable', 'date', 'after_or_equal:requested_date'],
            'received_date' => ['nullable', 'date', 'after_or_equal:requested_date'],
            'temporaryFile' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }

    /**
     * Approval/issuance details may only be set by users who can approve
     * requisitions (the UI hides these fields from everyone else); anyone
     * else changing them is rejected rather than silently trusted.
     */
    protected function guardApprovalFields(Requisition $requisition): void
    {
        if (Auth::user()?->can('approve', $requisition)) {
            return;
        }

        $normalize = fn ($value) => ($value === null || $value === '') ? null : (string) $value;

        $errors = [];

        foreach (['approved_by', 'issued_by', 'approved_date', 'issued_date'] as $field) {
            if ($normalize($this->{$field}) !== $normalize($requisition->{$field})) {
                $errors[$field] = 'Only an approver can set the approval and issuance details.';
            }
        }

        if ($errors) {
            throw ValidationException::withMessages(
                collect($errors)->mapWithKeys(fn ($message, $field) => [$this->getPropertyName() . '.' . $field => $message])->all()
            );
        }
    }

    public function toArray(): array
    {
        return [
            'ris' => $this->ris,
            'user_id' => $this->user_id,
            'requested_by' => $this->requested_by,
            'approved_by' => $this->approved_by,
            'issued_by' => $this->issued_by,
            'received_by' => $this->received_by,
            'pdf' => $this->pdf, // Use the renamed/stored file path
            'completed' => $this->completed ?? false,
            'purpose' => $this->purpose,
            'approved_date' => $this->approved_date,
            'issued_date' => $this->issued_date,
            'received_date' => $this->received_date,
            'requested_date' => $this->requested_date
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
            'completed' => false,
            'purpose' => null,
            'approved_date' => null,
            'issued_date' => null,
            'received_date' => null,
            'requested_date' => null
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
        $this->purpose = $requisition->purpose;
        $this->approved_date = $requisition->approved_date;
        $this->issued_date = $requisition->issued_date;
        $this->received_date = $requisition->received_date;
        $this->requested_date = $requisition->requested_date;
    }
}
