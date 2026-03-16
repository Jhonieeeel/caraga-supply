{{-- ===================== MEALS ===================== --}}
@foreach ($mealForm->blocks as $blockIndex => $block)
    <details class="border rounded-lg p-4 mb-6 bg-gray-50" open>
        <summary class="flex justify-between items-center cursor-pointer mb-4">
            <div>
                <h3 class="text-lg font-semibold">Lot Block {{ $blockIndex + 1 }}</h3>
                <p class="text-sm text-gray-600">
                    Total: ₱{{ number_format($this->getBlockTotal($blockIndex), 2) }}
                </p>
            </div>
            @if ($blockIndex > 0)
                <x-button flat negative icon="trash" wire:click="removeBlock({{ $blockIndex }})" />
            @endif
        </summary>

        {{-- Main Information --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <x-input wire:model="mealForm.blocks.{{ $blockIndex }}.lot_title" label="Lot Title *" />
            <x-input wire:model="mealForm.blocks.{{ $blockIndex }}.location" label="Location *" />
            <x-input wire:model="mealForm.blocks.{{ $blockIndex }}.date" label="Date *"
                hint="e.g., 'July 23, 2025' or 'Within CY 2026'" />
        </div>

        {{-- Add New Meal Item --}}
        <details class="mb-6 p-4 border rounded bg-white" open>
            <summary class="cursor-pointer font-medium text-sm mb-4">Add New Meal Item</summary>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <x-number wire:model="mealForm.currentLotItem.pax_qty" label="No. of Pax *" />
                <x-input wire:model="mealForm.currentLotItem.mealSnack" label="Meal/Snack *" />
                <x-input wire:model="mealForm.currentLotItem.arrangement" label="Serving Arrangement *" />
                <x-input wire:model="mealForm.currentLotItem.delivery_date" label="Inclusive Date/s *" />

                <div class="col-span-2">
                    <x-textarea wire:model="mealForm.currentLotItem.menu" resize-auto label="Menu *" />
                </div>
                <div class="col-span-2">
                    <x-textarea wire:model="mealForm.currentLotItem.other_requirement" resize-auto
                        label="Other Requirements/Remarks" />
                </div>

                <x-number wire:model="mealForm.currentLotItem.qty" label="Quantity *" />
                <x-select.styled wire:model="mealForm.currentLotItem.unit" label="Unit *" :options="[
                    ['label' => 'Pc', 'value' => 'pc'],
                    ['label' => 'Pack', 'value' => 'pack'],
                    ['label' => 'Sachet', 'value' => 'sachet'],
                    ['label' => 'Unit', 'value' => 'unit'],
                    ['label' => 'Ream', 'value' => 'ream'],
                    ['label' => 'Box', 'value' => 'box'],
                    ['label' => 'Set', 'value' => 'set'],
                    ['label' => 'Meter', 'value' => 'meter'],
                    ['label' => 'Kg', 'value' => 'kg'],
                    ['label' => 'Bag', 'value' => 'bag'],
                    ['label' => 'Case', 'value' => 'case'],
                    ['label' => 'Kit', 'value' => 'kit'],
                    ['label' => 'Lot', 'value' => 'lot'],
                    ['label' => 'Bucket', 'value' => 'bucket'],
                    ['label' => 'Galon', 'value' => 'galon'],
                    ['label' => 'Crate', 'value' => 'crate'],
                    ['label' => 'Bottle', 'value' => 'bottle'],
                    ['label' => 'Pax', 'value' => 'pax'],
                ]" />

                <x-number wire:model="mealForm.currentLotItem.estimated_unit_cost" label="Estimated Unit Cost *"
                    prefix="₱" step="0.01" />

                <div class="col-span-2 flex items-center justify-between">
                    <x-button icon="plus" position="left" wire:click="addLotItem({{ $blockIndex }})">
                        Add to Lot Block {{ $blockIndex + 1 }}
                    </x-button>
                </div>
            </div>
        </details>

        {{-- Meal Items Table --}}
        @if (!empty($block['items']))
            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-medium text-sm mb-0">Meal Items ({{ count($block['items']) }})</h4>
                    <p class="text-sm font-semibold text-gray-700">
                        Items Subtotal: ₱{{ number_format($this->sumItemsPublic($block['items']), 2) }}
                    </p>
                </div>

                <div class="overflow-x-auto border rounded-lg bg-white">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="text-left px-3 py-2">Meal/Snack</th>
                                <th class="text-left px-3 py-2">Menu</th>
                                <th class="text-left px-3 py-2">Pax</th>
                                <th class="text-left px-3 py-2">Qty</th>
                                <th class="text-left px-3 py-2">Unit</th>
                                <th class="text-right px-3 py-2">Unit Cost</th>
                                <th class="text-right px-3 py-2">Total</th>
                                <th class="text-right px-3 py-2">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @foreach ($block['items'] as $itemIndex => $item)
                                @php
                                    $isEditing =
                                        $mealForm->editing['type'] === 'lot' &&
                                        $mealForm->editing['blockIndex'] === $blockIndex &&
                                        $mealForm->editing['itemIndex'] === $itemIndex;
                                @endphp

                                @if (!$isEditing)
                                    <tr>
                                        <td class="px-3 py-2 font-medium">{{ $item['mealSnack'] ?? 'N/A' }}</td>
                                        <td class="px-3 py-2 text-gray-600 truncate max-w-[320px]">
                                            {{ $item['menu'] ?? 'N/A' }}</td>
                                        <td class="px-3 py-2">{{ $item['pax_qty'] ?? 0 }}</td>
                                        <td class="px-3 py-2">{{ $item['qty'] ?? 0 }}</td>
                                        <td class="px-3 py-2">{{ $item['unit'] ?? '' }}</td>
                                        <td class="px-3 py-2 text-right">
                                            ₱{{ number_format($item['estimated_unit_cost'] ?? 0, 2) }}</td>
                                        <td class="px-3 py-2 text-right font-semibold">
                                            ₱{{ number_format(($item['qty'] ?? 0) * ($item['estimated_unit_cost'] ?? 0), 2) }}
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex justify-end gap-2">
                                                <x-button sm icon="pencil"
                                                    wire:click="startEditLotItem({{ $blockIndex }}, {{ $itemIndex }})">
                                                    Edit
                                                </x-button>
                                                <x-button flat negative icon="trash"
                                                    wire:click="removeItem({{ $blockIndex }}, {{ $itemIndex }}, 'lot')" />
                                            </div>
                                        </td>
                                    </tr>
                                @else
                                    <tr class="bg-yellow-50">
                                        <td class="px-3 py-3" colspan="8">
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                                <x-number wire:model="mealForm.editForm.pax_qty" label="No. of Pax *" />
                                                <x-input wire:model="mealForm.editForm.mealSnack"
                                                    label="Meal/Snack *" />
                                                <x-input wire:model="mealForm.editForm.arrangement"
                                                    label="Serving Arrangement *" />
                                                <x-input wire:model="mealForm.editForm.delivery_date"
                                                    label="Inclusive Date/s *" />

                                                <div class="col-span-2">
                                                    <x-textarea wire:model="mealForm.editForm.menu" resize-auto
                                                        label="Menu *" />
                                                </div>
                                                <div class="col-span-2">
                                                    <x-textarea wire:model="mealForm.editForm.other_requirement"
                                                        resize-auto label="Other Requirements/Remarks" />
                                                </div>

                                                <x-number wire:model="mealForm.editForm.qty" label="Quantity *" />
                                                <x-select.styled wire:model="mealForm.editForm.unit" label="Unit *"
                                                    :options="[
                                                        ['label' => 'Pc', 'value' => 'pc'],
                                                        ['label' => 'Pack', 'value' => 'pack'],
                                                        ['label' => 'Sachet', 'value' => 'sachet'],
                                                        ['label' => 'Unit', 'value' => 'unit'],
                                                        ['label' => 'Ream', 'value' => 'ream'],
                                                        ['label' => 'Box', 'value' => 'box'],
                                                        ['label' => 'Set', 'value' => 'set'],
                                                        ['label' => 'Meter', 'value' => 'meter'],
                                                        ['label' => 'Kg', 'value' => 'kg'],
                                                        ['label' => 'Bag', 'value' => 'bag'],
                                                        ['label' => 'Case', 'value' => 'case'],
                                                        ['label' => 'Kit', 'value' => 'kit'],
                                                        ['label' => 'Lot', 'value' => 'lot'],
                                                        ['label' => 'Bucket', 'value' => 'bucket'],
                                                        ['label' => 'Galon', 'value' => 'galon'],
                                                        ['label' => 'Crate', 'value' => 'crate'],
                                                        ['label' => 'Bottle', 'value' => 'bottle'],
                                                        ['label' => 'Pax', 'value' => 'pax'],
                                                    ]" />

                                                <x-number wire:model="mealForm.editForm.estimated_unit_cost"
                                                    label="Estimated Unit Cost *" prefix="₱" step="0.01" />

                                                <div class="col-span-2 flex items-end justify-between">
                                                    <p class="text-sm font-semibold text-gray-800">
                                                        Edited Total:
                                                        ₱{{ number_format(($mealForm->editForm['qty'] ?? 0) * ($mealForm->editForm['estimated_unit_cost'] ?? 0), 2) }}
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

        {{-- Accommodation Blocks --}}
        @if (!empty($block['accommodations']))
            <div class="mt-6">
                <h4 class="text-md font-semibold mb-4">Accommodation Blocks</h4>

                @foreach ($block['accommodations'] as $accommodationIndex => $accommodation)
                    <details class="border rounded-lg p-4 mb-4 bg-blue-50" open>
                        <summary class="flex justify-between items-center cursor-pointer mb-4">
                            <h5 class="font-medium">Accommodation Block {{ $accommodationIndex + 1 }}</h5>
                            <x-button flat negative icon="trash"
                                wire:click="removeAccommodationBlock({{ $blockIndex }}, {{ $accommodationIndex }})" />
                        </summary>

                        {{-- Accommodation Main Information --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <x-input
                                wire:model="mealForm.blocks.{{ $blockIndex }}.accommodations.{{ $accommodationIndex }}.accommodation_title"
                                label="Accommodation Title *" />
                            <x-input
                                wire:model="mealForm.blocks.{{ $blockIndex }}.accommodations.{{ $accommodationIndex }}.location"
                                label="Location *" />
                            <x-input
                                wire:model="mealForm.blocks.{{ $blockIndex }}.accommodations.{{ $accommodationIndex }}.date"
                                label="Date *" />
                        </div>

                        {{-- Add New Accommodation Item --}}
                        <details class="mb-6 p-4 border rounded bg-white" open>
                            <summary class="cursor-pointer font-medium text-sm mb-4">Add New Accommodation Item
                            </summary>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <x-number wire:model="mealForm.currentAccommodationItem.no_days"
                                    label="No. of Days *" />
                                <x-input wire:model="mealForm.currentAccommodationItem.room_type"
                                    label="Room Type *" />
                                <x-input wire:model="mealForm.currentAccommodationItem.room_arrangement"
                                    label="Room Arrangement *" />
                                <x-input wire:model="mealForm.currentAccommodationItem.inclusive_dates"
                                    label="Inclusive Date/s *" />

                                <div class="col-span-2">
                                    <x-textarea wire:model="mealForm.currentAccommodationItem.remarks" resize-auto
                                        label="Remarks *" />
                                </div>
                                <div class="col-span-2">
                                    <x-textarea wire:model="mealForm.currentAccommodationItem.other_requirement"
                                        resize-auto label="Other Requirements" />
                                </div>

                                <x-number wire:model="mealForm.currentAccommodationItem.qty" label="Quantity *" />
                                <x-select.styled wire:model="mealForm.currentAccommodationItem.unit" label="Unit *"
                                    :options="[
                                        ['label' => 'Room', 'value' => 'room'],
                                        ['label' => 'Day', 'value' => 'day'],
                                        ['label' => 'Night', 'value' => 'night'],
                                        ['label' => 'Unit', 'value' => 'unit'],
                                    ]" />

                                <x-number wire:model="mealForm.currentAccommodationItem.estimated_unit_cost"
                                    label="Estimated Unit Cost *" prefix="₱" step="0.01" />

                                <div class="col-span-2 flex items-center justify-between">
                                    <x-button icon="plus" position="left"
                                        wire:click="addAccommodationItem({{ $blockIndex }}, {{ $accommodationIndex }})">
                                        Add to Accommodation Block {{ $accommodationIndex + 1 }}
                                    </x-button>
                                </div>
                            </div>
                        </details>

                        {{-- Accommodation Items List --}}
                        @if (!empty($accommodation['items']))
                            <div class="mb-4">
                                <h6 class="text-sm font-medium mb-2">
                                    Accommodation Items ({{ count($accommodation['items']) }})
                                </h6>

                                <div class="space-y-2">
                                    @foreach ($accommodation['items'] as $itemIndex => $item)
                                        @php
                                            $isEditing =
                                                $mealForm->editing['type'] === 'accommodation' &&
                                                $mealForm->editing['blockIndex'] === $blockIndex &&
                                                $mealForm->editing['accommodationIndex'] === $accommodationIndex &&
                                                $mealForm->editing['itemIndex'] === $itemIndex;
                                        @endphp

                                        @if (!$isEditing)
                                            <div class="flex justify-between items-center p-2 border rounded bg-white">
                                                <div class="text-sm">
                                                    <span class="font-medium">{{ $item['room_type'] ?? 'N/A' }}</span>
                                                    <span class="text-gray-600 ml-2">
                                                        (Days: {{ $item['no_days'] ?? 0 }} |
                                                        Qty: {{ $item['qty'] ?? 0 }} {{ $item['unit'] ?? '' }} |
                                                        Unit Cost:
                                                        ₱{{ number_format($item['estimated_unit_cost'] ?? 0, 2) }} |
                                                        Total:
                                                        ₱{{ number_format(($item['qty'] ?? 0) * ($item['estimated_unit_cost'] ?? 0), 2) }})
                                                    </span>
                                                </div>
                                                <div class="flex gap-2">
                                                    <x-button sm icon="pencil"
                                                        wire:click="startEditAccommodationItem({{ $blockIndex }}, {{ $accommodationIndex }}, {{ $itemIndex }})">
                                                        Edit
                                                    </x-button>
                                                    <x-button flat negative icon="trash"
                                                        wire:click="removeItem({{ $blockIndex }}, {{ $itemIndex }}, 'accommodation', {{ $accommodationIndex }})" />
                                                </div>
                                            </div>
                                        @else
                                            <div class="p-3 border rounded bg-yellow-50">
                                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                                    <x-number wire:model="mealForm.editForm.no_days"
                                                        label="No. of Days *" />
                                                    <x-input wire:model="mealForm.editForm.room_type"
                                                        label="Room Type *" />
                                                    <x-input wire:model="mealForm.editForm.room_arrangement"
                                                        label="Room Arrangement *" />
                                                    <x-input wire:model="mealForm.editForm.inclusive_dates"
                                                        label="Inclusive Date/s *" />

                                                    <div class="col-span-2">
                                                        <x-textarea wire:model="mealForm.editForm.remarks" resize-auto
                                                            label="Remarks *" />
                                                    </div>
                                                    <div class="col-span-2">
                                                        <x-textarea wire:model="mealForm.editForm.other_requirement"
                                                            resize-auto label="Other Requirements" />
                                                    </div>

                                                    <x-number wire:model="mealForm.editForm.qty" label="Quantity *" />
                                                    <x-select.styled wire:model="mealForm.editForm.unit"
                                                        label="Unit *" :options="[
                                                            ['label' => 'Room', 'value' => 'room'],
                                                            ['label' => 'Day', 'value' => 'day'],
                                                            ['label' => 'Night', 'value' => 'night'],
                                                            ['label' => 'Unit', 'value' => 'unit'],
                                                        ]" />

                                                    <x-number wire:model="mealForm.editForm.estimated_unit_cost"
                                                        label="Estimated Unit Cost *" prefix="₱"
                                                        step="0.01" />

                                                    <div class="col-span-2 flex items-end justify-between">
                                                        <p class="text-sm font-semibold">
                                                            Edited Total:
                                                            ₱{{ number_format(($mealForm->editForm['qty'] ?? 0) * ($mealForm->editForm['estimated_unit_cost'] ?? 0), 2) }}
                                                        </p>
                                                        <div class="flex gap-2">
                                                            <x-button sm color="green" icon="check"
                                                                wire:click="saveEdit">Save</x-button>
                                                            <x-button sm flat icon="x-mark"
                                                                wire:click="cancelEdit">Cancel</x-button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </details>
                @endforeach
            </div>
        @endif

        {{-- Add Accommodation Block Button --}}
        <div class="mt-4">
            <x-button outline icon="building-office" wire:click="addAccommodationBlock({{ $blockIndex }})">
                Add Accommodation Block to this Lot
            </x-button>
        </div>

    </details>
@endforeach

{{-- Action Buttons --}}
<div class="flex flex-wrap gap-3 mt-6">
    <x-button icon="plus" wire:click="addLotBlock">Add Another Lot Block</x-button>
    <x-button icon="bookmark" color="blue" wire:click="saveMealDraft">Save Draft</x-button>
    <x-button icon="printer" flat color="green" position="left" wire:click="printPR">Print Purchase
        Request</x-button>
</div>

