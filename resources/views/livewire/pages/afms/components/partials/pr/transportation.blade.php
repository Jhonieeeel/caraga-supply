{{-- ===================== TRANSPORTATION ===================== --}}
<div class="space-y-6">

    {{-- Marker Fields --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-input wire:model="transportationForm.delivery_period" label="Delivery Period *"
            hint="e.g., 'July 23, 2025' or 'Within CY 2026'" />
        <x-input wire:model="transportationForm.delivery_site" label="Delivery Site *" />
        <x-input wire:model="transportationForm.pick_up" label="Pick-up Point *" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-input wire:model="transportationForm.reqs_vehicle" label="Type of Vehicle *" />
        <x-input wire:model="transportationForm.reqs_model" label="Vehicle Model *" />
        <x-input wire:model="transportationForm.reqs_number" label="Plate / Unit No. *" />
    </div>

    {{-- Add New Item --}}
    <details class="p-4 border rounded bg-gray-50" open>
        <summary class="cursor-pointer font-medium text-sm mb-4">Add New Item</summary>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-4">
            <x-number wire:model="transportationForm.currentItem.pax_qty" label="No. of Pax" />
            <x-input wire:model="transportationForm.currentItem.itinerary" label="Itinerary *" />
            <x-input wire:model="transportationForm.currentItem.date_time" label="Date/Time *"
                placeholder="e.g., July 23, 2025 8:00 AM" />
            <x-number wire:model="transportationForm.currentItem.no_of_vehicles" label="No. of Vehicles *" />
            <x-number wire:model="transportationForm.currentItem.estimated_unit_cost" label="Estimated Unit Cost *"
                prefix="₱" step="0.01" />

            <div class="col-span-3 flex items-center justify-between">
                <p class="text-sm font-medium">
                    Estimated Cost:
                    ₱{{ number_format(($transportationForm->currentItem['no_of_vehicles'] ?? 0) * ($transportationForm->currentItem['estimated_unit_cost'] ?? 0), 2) }}
                </p>
                <x-button icon="plus" wire:click="addTransportationItem">
                    Add Item
                </x-button>
            </div>
        </div>
    </details>

    {{-- Items Table --}}
    @if (!empty($transportationForm->items))
        <div>
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-sm">Items ({{ count($transportationForm->items) }})</h4>
                <p class="text-sm font-semibold text-gray-700">
                    Total: ₱{{ number_format($transportationForm->getTotal(), 2) }}
                </p>
            </div>

            <div class="overflow-x-auto border rounded-lg bg-white">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="text-left px-3 py-2">Pax</th>
                            <th class="text-left px-3 py-2">Itinerary</th>
                            <th class="text-left px-3 py-2">Date/Time</th>
                            <th class="text-left px-3 py-2">Vehicles</th>
                            <th class="text-right px-3 py-2">Unit Cost</th>
                            <th class="text-right px-3 py-2">Total</th>
                            <th class="text-right px-3 py-2">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @foreach ($transportationForm->items as $itemIndex => $item)
                            @php
                                $isEditing = $transportationForm->editing['itemIndex'] === $itemIndex;
                            @endphp

                            @if (!$isEditing)
                                <tr>
                                    <td class="px-3 py-2">{{ $item['pax_qty'] ?? 0 }}</td>
                                    <td class="px-3 py-2 font-medium">{{ $item['itinerary'] ?? 'N/A' }}</td>
                                    <td class="px-3 py-2 text-gray-600">{{ $item['date_time'] ?? 'N/A' }}</td>
                                    <td class="px-3 py-2">{{ $item['no_of_vehicles'] ?? 0 }}</td>
                                    <td class="px-3 py-2 text-right">
                                        ₱{{ number_format($item['estimated_unit_cost'] ?? 0, 2) }}
                                    </td>
                                    <td class="px-3 py-2 text-right font-semibold">
                                        ₱{{ number_format(($item['no_of_vehicles'] ?? 0) * ($item['estimated_unit_cost'] ?? 0), 2) }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="flex justify-end gap-2">
                                            <x-button sm icon="pencil"
                                                wire:click="startEditTransportationItem({{ $itemIndex }})">
                                                Edit
                                            </x-button>
                                            <x-button flat negative icon="trash"
                                                wire:click="removeTransportationItem({{ $itemIndex }})" />
                                        </div>
                                    </td>
                                </tr>
                            @else
                                <tr class="bg-yellow-50">
                                    <td class="px-3 py-3" colspan="7">
                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                            <x-number wire:model="transportationForm.editForm.pax_qty"
                                                label="No. of Pax" />
                                            <x-input wire:model="transportationForm.editForm.itinerary"
                                                label="Itinerary *" />
                                            <x-input wire:model="transportationForm.editForm.date_time"
                                                label="Date/Time *" />
                                            <x-number wire:model="transportationForm.editForm.no_of_vehicles"
                                                label="No. of Vehicles *" />
                                            <x-number wire:model="transportationForm.editForm.estimated_unit_cost"
                                                label="Estimated Unit Cost *" prefix="₱" step="0.01" />

                                            <div class="col-span-2 flex items-end justify-between">
                                                <p class="text-sm font-semibold">
                                                    Edited Total:
                                                    ₱{{ number_format(($transportationForm->editForm['no_of_vehicles'] ?? 0) * ($transportationForm->editForm['estimated_unit_cost'] ?? 0), 2) }}
                                                </p>
                                                <div class="flex gap-2">
                                                    <x-button sm color="green" icon="check"
                                                        wire:click="saveEdit">Save</x-button>
                                                    <x-button sm flat icon="x-mark"
                                                        wire:click="cancelEdit">Cancel</x-button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Action Buttons --}}
    <div class="flex flex-wrap gap-3 pt-2">
        <x-button icon="bookmark" color="blue" wire:click="saveTransportationDraft">
            Save Draft
        </x-button>
        <x-button icon="printer" flat color="green" wire:click="printPR">
            Print Purchase Request
        </x-button>
    </div>

</div>

