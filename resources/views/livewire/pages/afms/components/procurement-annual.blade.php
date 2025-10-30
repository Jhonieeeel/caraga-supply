<div>
    <div class="flex justify-between items-center">
        <h4 class="text-sm">Annual Procurement Plan</h4>
        <x-button x-on:click="$modalOpen('add-entry')" icon="cube" position="right">Add
            Entry</x-button>
    </div>

    <div>
        <x-table :$headers id="procurement" :rows='$this->rows' loading filter paginate :quantity="[2, 5, 10]">
            @interact('column_code', $procurement)
                <span class="text-nowrap">{{ $procurement->code }}</span>
            @endinteract
            @interact('column_remarks', $procurement)
                <span class="whitespace-nowrap">{{ $procurement->remarks }}</span>
            @endinteract
            @interact('column_action', $procurement)
                <div class="sm:flex items-center gap-x-4">
                    <x-button.circle color="teal" icon="pencil" wire:click="edit({{ $procurement->id }})" />
                    <x-button.circle color="teal" icon="document-text"
                        wire:click="submitToRequest({{ $procurement->id }})" />
                </div>
            @endinteract
        </x-table>
    </div>

    {{-- edit --}}
    <x-modal title="Annual Procurment Plan" id="edit-entry" size="4xl" scrollable>
        <div class="sm:py-4 py-2">
            <p class="text-sm text-gray-500">Annual Procurement Plan Edit Form</p>
        </div>

        <form class="grid grid-cols-2 gap-4 w-full" wire:submit.prevent='onUpdate'>
            <div class="col-span-2">
                <x-textarea label="Project Title *" wire:model="annualForm.project_title" />
            </div>
            <x-input label="Code (PAP) *" wire:model="annualForm.code" />
            <x-input label="Notice of Award *" wire:model="annualForm.notice_of_award" />
            <x-input label="Contract Signing *" wire:model="annualForm.contract_signing" />
            <x-input label="Source of Funds *" wire:model="annualForm.source_of_funds" />
            <x-input label="Estimated Budget (Total) *" wire:model="annualForm.estimated_budget_total" />
            <x-input label="Estimated Budget (MOOE) *" wire:model="annualForm.estimated_budget_mooe" />
            <x-input label="Estimated Budget (CO) *" wire:model="annualForm.estimated_budget_co" />
            <x-input label="PMO/End User *" wire:model="annualForm.pmo_end_user" />
            <x-input label="Is this an Early Procurement Activity? (Yes/No) *" wire:model="annualForm.early_activity" />
            <x-select.styled label="Mode of Procurement *" wire:model="annualForm.mode_of_procurement"
                :options="[
                    'NP-50-Direct Contracting',
                    'NP-51-Repeat Order',
                    'NP-52.1b-Shopping',
                    'NP-53.1-Two Failed Biddings',
                    'NP-53.2-Emergency Cases',
                    'NP-53.5-Agency-to-Agency',
                    'NP-53.6-Scientific, Scholarly, or Artistic Work, Exclusive Technology and Media Services',
                    'NP-53.7-Highly Technical Consultant',
                    'NP-53.9-Small Value Procurement',
                    'NP-53.10-Lease of Real Property and Venue',
                ]" />
            <x-input label="Advertisement/Posting of IB/REI *" wire:model="annualForm.advertisement_posting" />
            <x-input label="Submission/Opening of Bids *" wire:model="annualForm.submission_bids" />
            <x-select.styled label="Select Year *" :options="$years" wire:model="annualForm.app_year" />
            <div class="col-span-2">
                <x-textarea label="Remarks *" wire:model="annualForm.remarks" />
            </div>
            <div class="col-span-2 ml-auto">
                <x-button submit icon="cube" position="right">Submit</x-button>
            </div>
        </form>
    </x-modal>

    {{-- add --}}
    <x-modal title="Annual Procurment Plan" id="add-entry" size="4xl" scrollable>

        <div class="sm:py-4 py-2">
            <p class="text-sm text-gray-500">Annual Procurement Plan Form</p>
        </div>

        <form class="grid grid-cols-2 gap-4 w-full" wire:submit.prevent='onSubmit'>
            <div class="col-span-2">
                <x-textarea label="Project Title *" wire:model="annualForm.project_title" />
            </div>
            <x-input label="Code (PAP) *" wire:model="annualForm.code" />
            <x-input label="Notice of Award *" wire:model="annualForm.notice_of_award" />
            <x-input label="Contract Signing *" wire:model="annualForm.contract_signing" />
            <x-input label="Source of Funds *" wire:model="annualForm.source_of_funds" />
            <x-number label="Estimated Budget (Total) *" wire:model="annualForm.estimated_budget_total" />
            <x-number label="Estimated Budget (MOOE) *" wire:model="annualForm.estimated_budget_mooe" />
            <x-number label="Estimated Budget (CO) *" wire:model="annualForm.estimated_budget_co" />
            <x-input label="PMO/End User *" wire:model="annualForm.pmo_end_user" />
            <x-input label="Is this an Early Procurement Activity? (Yes/No) *" wire:model="annualForm.early_activity" />
            <x-select.styled label="Mode of Procurement *" wire:model="annualForm.mode_of_procurement"
                :options="[
                    'NP-50-Direct Contracting',
                    'NP-51-Repeat Order',
                    'NP-52.1b-Shopping',
                    'NP-53.1-Two Failed Biddings',
                    'NP-53.2-Emergency Cases',
                    'NP-53.5-Agency-to-Agency',
                    'NP-53.6-Scientific, Scholarly, or Artistic Work, Exclusive Technology and Media Services',
                    'NP-53.7-Highly Technical Consultant',
                    'NP-53.9-Small Value Procurement',
                    'NP-53.10-Lease of Real Property and Venue',
                ]" />
            <x-input label="Advertisement/Posting of IB/REI *" wire:model="annualForm.advertisement_posting" />
            <x-input label="Submission/Opening of Bids *" wire:model="annualForm.submission_bids" />
            <x-select.styled label="Select Year *" :options="$years" wire:model="annualForm.app_year" />
            <div class="col-span-2">
                <x-textarea label="Remarks *" wire:model="annualForm.remarks" />
            </div>
            <div class="col-span-2 ml-auto">
                <x-button submit icon="cube" position="right">Submit</x-button>
            </div>
        </form>
    </x-modal>

</div>

