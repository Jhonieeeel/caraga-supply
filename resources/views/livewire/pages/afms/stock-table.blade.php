<div>
    <div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8">
        <div class="flex items-center justify-between sm:pb-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Stock
            </h2>

            <x-button x-on:click="$modalOpen('add')" icon="cube" position="right">Add Stock</x-button>
        </div>
        <div class="overflow-hidden sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <x-table :$headers :rows='$this->rows' filter :quantity="[2, 5, 10]" loading paginate>
                    @interact('column_action', $stock)
                        <x-button color="red" icon="trash" wire:click='delete({{ $stock->id }})' />
                        <x-button color="teal" icon="pencil-square" wire:click='edit({{ $stock }})' />
                    @endinteract
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
                <div class="sm:col-span-2 ms-auto">
                    <x-button submit text="Submit" color="primary" />
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
                <div class="sm:col-span-2 ms-auto">
                    <x-button submit text="Submit" color="primary" />
                </div>
            </form>
        </x-modal>
    </div>
</div>

