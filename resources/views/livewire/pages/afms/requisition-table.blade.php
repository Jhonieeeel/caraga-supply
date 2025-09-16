<div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-6 bg-white border shadow rounded">
    <div class="flex items-center justify-between sm:pb-4">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Requisition
        </h2>

        <x-button x-on:click="$modalOpen('add-request')" icon="cube" position="right">Add Request</x-button>
    </div>
    <div class="overflow-hidden sm:rounded-lg">
        <div class="sm:p-3 text-gray-900">
            @if (session('message'))
                <div class="sm:py-4">
                    <x-alert title="{{ session('message')['title'] }}" text="{{ session('message')['text'] }}"
                        color="{{ session('message')['color'] }}" light />
                </div>
            @endif
            <x-tab wire:model.live="tab">

                <x-tab.items tab="List">
                    <x-slot:right>
                        <x-icon name="list-bullet" class="w-5 h-5" />
                    </x-slot:right>
                    {{-- request table --}}
                    <livewire:pages.afms.components.request-table />
                </x-tab.items>
                <x-tab.items tab="Detail">
                    <x-slot:right>
                        <x-icon name="magnifying-glass" class="w-5 h-5" />
                    </x-slot:right>
                    {{-- request detail --}}
                    <livewire:pages.afms.components.request-detail />
                </x-tab.items>
                <x-tab.items tab="RIS">
                    <x-slot:right>
                        <x-icon name="document" class="w-5 h-5" />
                    </x-slot:right>
                    {{-- ris --}}
                    <livewire:pages.afms.components.request-r-i-s />
                </x-tab.items>
                @role('Super Admin')
                    <x-tab.items tab="RSMI">
                        <x-slot:right>
                            <x-icon name="cog-6-tooth" class="w-5 h-5" />
                        </x-slot:right>
                        {{-- rsmi --}}
                        <livewire:pages.afms.components.request-rsmi />
                    </x-tab.items>
                    <x-tab.items tab="RPCI (Stock Card)">
                        <x-slot:right>
                            <x-icon name="document-chart-bar" class="w-5 h-5" />
                        </x-slot:right>
                        {{-- rpci stock card --}}
                        <livewire:pages.afms.components.request-rpci />
                    </x-tab.items>
                @endrole
            </x-tab>
        </div>
    </div>

    {{-- add request --}}
    <x-modal title="Stocks Available" id="add-request" size="3xl">
        <p class="text-sm text-gray-500">Select a stock then add your quantity.</p>
        <form wire:submit.prevent='create'>
            <x-table :$headers :rows="$this->rows" filter :quantity="[3, 5, 10]" loading paginate>
                @interact('column_action', $stock)
                    <x-input type='number' min='1' max="{{ $stock->quantity }}"
                        wire:model="itemForm.requestedItems.{{ $stock->id }}" />
                @endinteract
            </x-table>
            @error('itemForm.requestedItems')
                <span class="text-red-600 text-sm my-2">{{ $message }}</span>
            @enderror
            <div class="sm:pt-4 py-2 flex justify-end items-center">
                <x-button submit icon="cube" position="right">Submit</x-button>
            </div>
        </form>
    </x-modal>

</div>

