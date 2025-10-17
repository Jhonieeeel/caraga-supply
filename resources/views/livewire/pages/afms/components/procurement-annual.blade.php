<div>
    <div class="flex justify-between items-center">
        <h4 class="text-sm">Annual Procurement Plan</h4>
        <x-button x-on:click="$modalOpen('add-entry')" icon="cube" position="right">Add
            Entry</x-button>
    </div>

    <div>
        <small class="text-xs font-gray-700">
            table goes here..
        </small>
    </div>

    <x-modal title="Annual Procurment Plan" id="add-entry" size="4xl">

        <div class="sm:py-4 py-2">
            <p class="text-sm text-gray-500">Annual Procurement Plan.</p>
        </div>

        <div class="grid grid-cols-2 gap-4 w-full">
            <x-input label="Code (PAP) *" />
            <x-input label="Notice of Award *" />
            <div class="col-span-2">
                <x-textarea label="Project Title *" />
            </div>
            <x-input label="Source of Funds *" />
            <x-input label="Estimated Budget (Total) *" />
            <x-input label="Estimated Budget (MOOE) *" />
            <x-input label="Estimated Budget (CO) *" />
            <x-input label="Estimated Budget (MOOE) *" />
            <x-input label="PMO/End User *" />
            <x-input label="Is this an Early Procurement Activity? (Yes/No) *" />
            <x-input label="Mode of Procurement *" />
            <x-input label="Advertisement/Posting of IB/REI *" />
            <x-input label="Submission/Opening of Bids *" />
            <x-select.native label="Select One Option" :options="[1, 2, 3]" />
            <div class="col-span-2">
                <x-textarea label="Remarks *" />
            </div>
            <div class="col-span-2 ml-auto">
                <x-button x-on:click="$modalOpen('add-entry')" icon="cube" position="right">Submit</x-button>
            </div>
        </div>
    </x-modal>
</div>

