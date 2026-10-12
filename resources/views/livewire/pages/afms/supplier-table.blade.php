<div>
    <div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8 bg-white border shadow rounded">
        <div class="flex items-center justify-between sm:pb-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Suppliers
            </h2>
            @can('manage-procurement')
                <x-button md x-on:click="$modalOpen('add')" icon="building-storefront" position="right">Add Supplier</x-button>
            @endcan
        </div>
        <div class="overflow-hidden sm:rounded-lg">
            <div class="p-6 text-gray-900">
                @if (session('message'))
                    <div class="sm:py-4">
                        <x-alert title="{{ session('message')['title'] }}" text="{{ session('message')['text'] }}"
                            color="{{ session('message')['color'] }}" light />
                    </div>
                @endif
                <x-table :$headers :rows='$this->rows' :placeholders="[
                    'quantity' => 'Items',
                ]" :filter="['quantity' => 'quantity', 'search' => 'search']" :quantity="[2, 5, 10]" paginate
                    loading>
                    @interact('column_is_eligible', $supplier)
                        <x-badge :color="$supplier->is_eligible ? 'teal' : 'red'" flat>
                            {{ $supplier->is_eligible ? 'Eligible' : 'Not Eligible' }}
                        </x-badge>
                    @endinteract
                    @can('manage-procurement')
                        @interact('column_action', $supplier)
                            <x-button.circle color="{{ $supplier->is_eligible ? 'yellow' : 'teal' }}" flat
                                icon="check-badge" wire:click="toggleEligibility({{ $supplier->id }})" />
                            <x-button.circle color="teal" flat icon="pencil-square" wire:click='edit({{ $supplier }})' />
                            <x-button.circle color="red" flat icon="trash" wire:click="delete('{{ $supplier->id }}')" />
                        @endinteract
                    @endcan
                </x-table>
            </div>
        </div>

        {{-- add --}}
        <x-modal title="Add Supplier Form" id="add">
            <form wire:submit.prevent='create'>
                <div class="grid sm:grid-cols-2 sm:gap-3 gap-5">
                    <x-input wire:model='supplierForm.business_name' label="Business Name *" />
                    <x-input wire:model='supplierForm.tin' label="TIN *" />
                    <x-input wire:model='supplierForm.contact_person' label="Contact Person" />
                    <x-input wire:model='supplierForm.email' label="Email" />
                    <x-input wire:model='supplierForm.phone' label="Phone" />
                    <x-select.styled wire:model='supplierForm.philgeps_membership' label="PhilGEPS Membership *"
                        :options="[
                            ['label' => 'Red', 'value' => 'Red'],
                            ['label' => 'Platinum', 'value' => 'Platinum'],
                        ]" />
                    <x-input wire:model='supplierForm.philgeps_no' label="PhilGEPS Registration No." />
                    <div class="sm:col-span-2">
                        <x-textarea wire:model='supplierForm.address' label="Address" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-textarea wire:model='supplierForm.notes' label="Notes" />
                    </div>
                </div>
                <div class="sm:pt-2">
                    <x-button md submit icon="plus-circle" position="right">Submit</x-button>
                </div>
            </form>
        </x-modal>

        {{-- edit --}}
        <x-modal title="Edit Supplier Form" id="edit">
            <form wire:submit.prevent='update'>
                <div class="grid sm:grid-cols-2 sm:gap-3 gap-5">
                    <x-input wire:model='supplierForm.business_name' label="Business Name *" />
                    <x-input wire:model='supplierForm.tin' label="TIN *" />
                    <x-input wire:model='supplierForm.contact_person' label="Contact Person" />
                    <x-input wire:model='supplierForm.email' label="Email" />
                    <x-input wire:model='supplierForm.phone' label="Phone" />
                    <x-select.styled wire:model='supplierForm.philgeps_membership' label="PhilGEPS Membership *"
                        :options="[
                            ['label' => 'Red', 'value' => 'Red'],
                            ['label' => 'Platinum', 'value' => 'Platinum'],
                        ]" />
                    <x-input wire:model='supplierForm.philgeps_no' label="PhilGEPS Registration No." />
                    <div class="sm:col-span-2">
                        <x-textarea wire:model='supplierForm.address' label="Address" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-textarea wire:model='supplierForm.notes' label="Notes" />
                    </div>
                </div>
                <div class="sm:pt-2">
                    <x-button md submit icon="plus-circle" position="right">Submit</x-button>
                </div>
            </form>
        </x-modal>
    </div>
</div>
