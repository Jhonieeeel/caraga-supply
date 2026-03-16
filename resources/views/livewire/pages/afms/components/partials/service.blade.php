{{-- ===================== SERVICES ===================== --}}
<div class="space-y-6">

    {{-- Delivery Info --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-input wire:model="serviceForm.delivery_period" label="Delivery Period *"
            hint="e.g., 'July 23, 2025' or 'Within CY 2026'" />
        <x-input wire:model="serviceForm.delivery_site" label="Delivery Site *" />
    </div>

    {{-- Quantity / Unit / Cost --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-number wire:model="serviceForm.quantity" label="Quantity *" />
        <x-input wire:model="serviceForm.unit" label="Unit *" hint="e.g., Lot, Unit, Hour" />
        <x-number wire:model="serviceForm.estimated_unit_cost" label="Estimated Unit Cost *" prefix="₱"
            step="0.01" />
    </div>

    {{-- Computed Estimated Cost --}}
    <div class="p-4 bg-gray-50 rounded-lg border">
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700">Estimated Cost</span>
            <span class="text-lg font-bold text-gray-900">
                ₱{{ number_format(($serviceForm->quantity ?? 0) * ($serviceForm->estimated_unit_cost ?? 0), 2) }}
            </span>
        </div>
        <p class="text-xs text-gray-500 mt-1">Quantity × Estimated Unit Cost</p>
    </div>

    {{-- Technical Specifications --}}
    <div>
        <x-textarea wire:model="serviceForm.technical_specifications" label="Technical Specifications *" rows="6"
            hint="Describe the technical requirements for this service." />
    </div>

    {{-- Action Buttons --}}
    <div class="flex flex-wrap gap-3 pt-2">
        <x-button icon="bookmark" color="blue" wire:click="saveServiceDraft">
            Save Draft
        </x-button>
        <x-button icon="printer" flat color="green" wire:click="printPR">
            Print Purchase Request
        </x-button>
    </div>

</div>

