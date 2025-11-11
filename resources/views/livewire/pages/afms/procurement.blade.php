<div class="space-y-6">
    <div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8 bg-white border shadow rounded">
        <div class="flex items-center justify-between border-b border-gray-200 pb-3 sm:p-6 p-3">
            <h2 class="text-xl font-semibold text-gray-800 tracking-tight flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 7v4a1 1 0 001 1h3v2H4a1 1 0 00-1 1v4h18v-4a1 1 0 00-1-1h-3v-2h3a1 1 0 001-1V7H3zm5 2h8v2H8V9z" />
                </svg>
                Procurement
            </h2>

            <x-button x-on:click="$modalOpen('upload')" color="green" icon="document" position="left">
                Upload CSV
            </x-button>
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
    <x-loading loading="readCSV" />

    <x-modal id="upload" title="Upload APP CSV">
        <form wire:submit.prevent="readCSV" class="space-y-4" enctype="multipart/form-data">
            <x-upload accept="application/csv" wire:model="app_csv" label="APP/SPP (CSV) *"
                hint="Please upload APP/SPP CSV." />
            <div class="flex justify-end">
                <x-button submit icon="arrow-up-on-square" position="left">Upload CSV</x-button>
            </div>
        </form>
    </x-modal>
</div>

