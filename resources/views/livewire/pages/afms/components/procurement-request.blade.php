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
                    <x-button.circle color="teal" icon="magnifying-glass" />
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
            <x-input label="Code (PAP) *" />
            <x-input label="Closing Date *" />
            <div class="col-span-2">
                <x-textarea label="Project Title *" />
            </div>
            <x-input label="Purchase Request Number *" />
            <x-input label="ABC (Based on APP) *" />
            <x-input label="ABC *" />
            <x-input label="Date Posted *" />
            <x-input label="Input Date *" />
            <x-input label="Estimated Budget (Total) *" />
            {{-- FileInput  APP/SPP/(PDF) --}}
            {{-- <x-upload label="APP/SPP(PDF)" tip="Drag and drop your pdf file here" /> --}}
            <x-input label="Document Label (Filename)" />
            {{-- FileInput PhilGeps Posting PDF --}}
            <x-input accept="application/pdf" label="PhilGeps Posting Label *" />

            <x-input label="Email Posting *" />
            <x-select.native label="Select One Option" :options="[1, 2, 3]" />
            <div class="col-span-2">
                <x-textarea label="Remarks *" />
            </div>
            <div class="col-span-2 ml-auto">
                <x-button x-on:click="$modalOpen('add-entry')" icon="cube" position="right">Submit</x-button>
            </div>
        </form>
    </x-modal>
</div>

