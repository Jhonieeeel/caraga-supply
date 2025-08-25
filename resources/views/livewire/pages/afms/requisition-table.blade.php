<div>
    <div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8">
        <div class="flex items-center justify-between sm:pb-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Requisition
            </h2>

            <x-button x-on:click="$modalOpen('add')" icon="cube" position="right">Add Request</x-button>
        </div>
        <div class="overflow-hidden sm:rounded-lg">
            <div class="p-6 text-gray-900">
                {{-- content here --}}
            </div>
        </div>

        {{-- add request --}}
        <x-modal title="Stocks Available" id="add" size="3xl">
            <p class="text-sm text-gray-500">Select a stock then add your quantity.</p>
            <form wire:submit.prevent='create'>
                <x-table selectable wire:model.live="selectedStockIds" :$headers :rows="$this->rows" filter
                    :quantity="[3, 5, 10]" loading>
                    @interact('column_action', $stock)
                        @if (in_array($stock->id, $this->selectedStockIds))
                            <x-input type='number' min='1' max="{{ $stock->quantity }}"
                                wire:model="requestedQuantity.{{ $stock->id }}" hint='Qty' />
                        @endif
                    @endinteract
                </x-table>
                <div class="sm:pt-4 py-2 flex justify-end items-center">
                    <x-button submit icon="cube" position="right">Submit</x-button>
                </div>
            </form>
        </x-modal>

    </div>
</div>

