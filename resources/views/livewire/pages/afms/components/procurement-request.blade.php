<div>
    <div class="flex justify-between items-center">
        <h4 class="text-sm">Purchase Request</h4>
        <x-button x-on:click="$modalOpen('add-request')" icon="cube" position="right">Add
            Entry</x-button>
    </div>

    <div>
        <x-table :$headers :rows='$this->rows' filter :quantity="[2, 5, 10]">
            @interact('column_action', $request)
                <div class="sm:flex items-center gap-2">
                    <x-button.circle color="teal" icon="magnifying-glass"
                        wire:click="viewDetails({{ $request->procurement }})" />
                    <x-button.circle color="teal" icon="receipt-percent" wire:click="submitToOrder({{ $request }})" />
                </div>
            @endinteract
        </x-table>
    </div>

    <x-modal title="Purchase Requests" id="add-request" size="4xl">
        <div class="sm:py-4 py-2">
            <p class="text-sm text-gray-500">Purchase Requests.</p>
        </div>

        <form class="grid grid-cols-2 gap-4 w-full" enctype="multipart/form-data">
            {{-- dependent Drropdown --}}
            <x-select.styled wire:model.live='requestForm.procurement_id' label="Code (PAP) *" :options="$this->getAnnuals"
                searchable />

            <x-input label="Closing Date *" wire:model="requestForm.closing_date" />
            <x-input label="Purchase Request Number *" wire:model="requestForm.pr_number" />
            {{-- dependent Dropdown --}}
            <x-input label="ABC (Based on APP) *" wire:model="requestForm.abc_based_app" />

            <x-input label="ABC *" wire:model="requestForm.abc" />
            <x-input label="Date Posted *" wire:model="requestForm.date_posted" />
            <x-input label="Input Date *" wire:model="requestForm.input_date" />
            <x-upload accept="application/pdf" wire:model="requestForm.app_spp_pdf_file" label="APP/SPP (PDF)"
                hint="Please upload RIS document." tip="Upload our Signed RIS here" />
            <x-input label="APP/SPP (PDF) (Filename)" wire:model="requestForm.app_spp_pdf_filename" />
            {{-- pdfs --}}
            <x-upload accept="application/pdf" wire:model="requestForm.philgeps_pdf_file" label="Philgeps Posting (PDF)"
                hint="Please upload RIS document." tip="Upload our Signed RIS here" />
            <x-input label="Philgeps Posting (Filename)" wire:model="requestForm.philgeps_pdf_filename" />

            <x-input label="Email Posting *" wire:model="requestForm.email_posting" />

            {{-- dependent Dropdown --}}
            <x-select.native label="Select APP Year" :options="[1, 2, 3]" />

            <div class="col-span-2">
            </div>
            <div class="col-span-2 ml-auto">
                <x-button x-on:click="$modalOpen('add-entry')" icon="cube" position="right">Submit</x-button>
            </div>
        </form>
    </x-modal>
</div>

