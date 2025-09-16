<div>
    <div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8 bg-white border shadow rounded">
        <div class="flex items-center justify-between sm:pb-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Stock
            </h2>
            @role('Super Admin')
                <x-button md x-on:click="$modalOpen('add')" icon="cube" position="right">Add Stock</x-button>
            @endrole
        </div>
        <div class="overflow-hidden sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <x-table :$headers :rows='$this->rows' filter :quantity="[2, 5, 10]" loading paginate>
                    @role('Super Admin')
                        @interact('column_action', $stock)
                            <x-button.circle color="teal" icon="pencil-square" wire:click='edit({{ $stock }})' />
                            <x-button.circle color="blue" icon="plus" wire:click="selectStock({{ $stock }})" />
                        @endinteract
                    @endrole
                </x-table>
            </div>
        </div>

        {{-- add stock --}}
        <x-modal title="Add Stock Form" id="add">
            <form wire:submit.prevent='create' class="grid sm:grid-cols-2 sm:gap-4 gap-5">
                <div class="sm:col-span-2">
                    <x-select.styled wire:model='stockForm.supply_id' label="Select Supply *" :options="$this->getSupplies"
                        searchable />
                </div>
                <div class="sm:col-span-1">
                    <x-input wire:model='stockForm.barcode' label="Barcode *" hint="Insert your barcode" />
                </div>
                <div class="sm:col-span-1">
                    <x-input wire:model='stockForm.stock_number' label="Stock Number *" hint="Insert your stock no." />
                </div>
                <div class="sm:col-span-1">
                    <x-number wire:model='stockForm.quantity' min="1" label="Quantity *"
                        hint="Insert your quantity" />
                </div>
                <div class="sm:col-span-1">
                    <x-number wire:model='stockForm.price' min="1.0" label="Price *" step="1.0"
                        hint="Insert your price" />
                </div>
                <div class="sm:col-span-2">
                    <x-input wire:model='stockForm.stock_location' label="Stock Location *"
                        hint="Insert your stock location." />
                </div>
                <div class="sm:col-span-2 ms-auto">
                    <x-button md submit text="Submit" color="primary" />
                </div>
            </form>
        </x-modal>

        <x-modal title="Edit Stock Form" id="edit-stock">
            <form wire:submit.prevent='update' class="grid sm:grid-cols-2 sm:gap-4 gap-5">
                <div class="sm:col-span-2">
                    <x-select.styled wire:model='stockForm.supply_id' label="Select Supply *" :options="$this->getSupplies"
                        searchable />
                </div>
                <div class="sm:col-span-1">
                    <x-input wire:model='stockForm.barcode' label="Barcode *" hint="Insert your barcode" />
                </div>
                <div class="sm:col-span-1">
                    <x-input wire:model='stockForm.stock_number' label="Stock Number *" hint="Insert your stock no." />
                </div>
                <div class="sm:col-span-1">
                    <x-number wire:model='stockForm.quantity' min="1" label="Quantity *"
                        hint="Insert your quantity" />
                </div>
                <div class="sm:col-span-1">
                    <x-number wire:model='stockForm.price' min="1.0" label="Price *" step="0.01"
                        hint="Insert your price" />
                </div>
                <div class="sm:col-span-2">
                    <x-input wire:model='stockForm.stock_location' label="Stock Location *"
                        hint="Insert your stock location." />
                </div>
                <div class="sm:col-span-2 ms-auto sm:space-x-4">
                    <x-button md text="Delete" wire:click="delete({{ $stock?->id }})" color="red" outline />
                    <x-button md submit text="Submit" color="primary" />
                </div>
            </form>
        </x-modal>

        <x-modal title="Purchase Order Stock Form" id="partial-edit-stock">
            <form wire:submit.prevent='savePurchaseStock' class="grid sm:grid-cols-2 sm:gap-4 gap-5">
                <div class="sm:col-span-2">
                    <x-select.styled wire:model='stockForm.supply_id' disabled label="Select Supply *" :options="$this->getSupplies"
                        searchable />
                </div>
                <div class="sm:col-span-1">
                    <x-input wire:model='stockForm.barcode' label="Barcode *" hint="Insert your barcode" />
                </div>
                <div class="sm:col-span-1">
                    <x-input wire:model='stockForm.stock_number' disabled label="Stock Number *"
                        hint="Insert your stock no." />
                </div>
                <div class="sm:col-span-1">
                    <x-number wire:model='stockForm.quantity' min="1" label="Quantity *"
                        hint="Insert your quantity" />
                </div>
                <div class="sm:col-span-1">
                    <x-number wire:model='stockForm.price' min="1.0" label="Price *" step="0.01"
                        hint="Insert your price" />
                </div>
                <div class="sm:col-span-2">
                    <x-input wire:model='stockForm.stock_location' label="Stock Location *"
                        hint="Insert your stock location." />
                </div>
                <div class="sm:col-span-2 ms-auto flex">
                    <x-button md submit text="Submit" color="primary" />
                </div>
            </form>
        </x-modal>
    </div>
</div>

