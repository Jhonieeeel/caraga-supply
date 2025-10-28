<div class="space-y-6">
    <div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8 bg-white border shadow rounded">
        <div class="flex items-center justify-between sm:pb-4 sm:p-6 p-3">
            <h2 class="font-semibold sm:text-xl text-md text-gray-800 leading-tight ">
                Procurement
            </h2>

            {{-- x-on:click="$modalOpen('add-request')" --}}
            <x-button wire:click="procurement" icon="cube" position="right">Upload CSV</x-button>
        </div>

        @if (session('message'))
            <div class="sm:py-4">
                <x-alert title="{{ session('message')['title'] }}" text="{{ session('message')['text'] }}"
                    color="{{ session('message')['color'] }}" light />
            </div>
        @endif

        <div class="overflow-hidden sm:rounded-lg">
            <div class="sm:p-3 text-gray-900">
                <x-tab wire:model.live="tab">
                    <x-tab.items tab="Annual">
                        <x-slot:left>
                            <x-icon name="calendar" class="w-5 h-5" />
                        </x-slot:left>
                        <livewire:pages.afms.components.procurement-annual />
                    </x-tab.items>
                    <x-tab.items tab="Requests">
                        <x-slot:left>
                            <x-icon name="document-text" class="w-5 h-5" />
                        </x-slot:left>
                        <livewire:pages.afms.components.procurement-request />
                    </x-tab.items>
                    <x-tab.items tab="Orders">
                        <x-slot:left>
                            <x-icon name="receipt-percent" class="w-5 h-5" />
                        </x-slot:left>
                        <livewire:pages.afms.components.procurement-order />
                    </x-tab.items>
                </x-tab>
            </div>
        </div>
    </div>
</div>

