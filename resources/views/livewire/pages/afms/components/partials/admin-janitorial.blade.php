{{-- ===================== ADMIN & JANITORIAL ===================== --}}

{{-- Document-level fields — filled once, not per block --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <x-input wire:model="adminJanitorialForm.delivery_period" label="Delivery Period *" placeholder="e.g., January 2025" />
    <x-input wire:model="adminJanitorialForm.delivery_site" label="Delivery Site *"
        placeholder="e.g., CARAGA Regional Office" />
</div>

@foreach ($adminJanitorialForm->blocks as $blockIndex => $block)
    <details class="border rounded-lg p-4 mb-6 bg-gray-50" open>
        <summary class="flex justify-between items-center cursor-pointer mb-4">
            <div>
                <h3 class="text-lg font-semibold">
                    Lot Block {{ $blockIndex + 1 }}
                    @if (!empty($block['block_title']))
                        — {{ $block['block_title'] }}
                    @endif
                </h3>
                <p class="text-sm text-gray-600">
                    Total: ₱{{ number_format($adminJanitorialForm->getBlockTotal($blockIndex), 2) }}
                </p>
            </div>
            <x-button flat negative icon="trash" wire:click="removeAdminJanitorialBlock({{ $blockIndex }})" />
        </summary>

        {{-- Block Title --}}
        <div class="grid grid-cols-1 gap-4 mb-6">
            <x-input wire:model="adminJanitorialForm.blocks.{{ $blockIndex }}.block_title" label="Lot Title *"
                placeholder="e.g., Lot 1 - Administrative Supplies" />
        </div>

        {{-- ============ ADMINISTRATIVE SUPPLIES ============ --}}
        <div class="mb-6">
            <h4 class="font-semibold text-sm text-gray-700 mb-3">Administrative Supplies</h4>

            {{-- Add Admin Item Form --}}
            <details class="mb-4 p-4 border rounded bg-white" open>
                <summary class="cursor-pointer font-medium text-sm mb-4">Add Administrative Item</summary>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <x-input wire:model="adminJanitorialForm.currentAdminItem.item_name" label="Item Name *"
                            placeholder="Item description" />
                    </div>

                    <x-number wire:model="adminJanitorialForm.currentAdminItem.quantity" label="Quantity *" />

                    <x-select.styled wire:model="adminJanitorialForm.currentAdminItem.unit" label="Unit *"
                        :options="[
                            ['label' => 'Pc', 'value' => 'pc'],
                            ['label' => 'Pack', 'value' => 'pack'],
                            ['label' => 'Box', 'value' => 'box'],
                            ['label' => 'Set', 'value' => 'set'],
                            ['label' => 'Unit', 'value' => 'unit'],
                            ['label' => 'Ream', 'value' => 'ream'],
                            ['label' => 'Bottle', 'value' => 'bottle'],
                            ['label' => 'Can', 'value' => 'can'],
                            ['label' => 'Roll', 'value' => 'roll'],
                            ['label' => 'Liter', 'value' => 'liter'],
                            ['label' => 'Kg', 'value' => 'kg'],
                        ]" />

                    <x-number wire:model="adminJanitorialForm.currentAdminItem.estimated_unit_cost"
                        label="Estimated Unit Cost *" prefix="₱" step="0.01" />

                    <div class="col-span-3 flex items-center justify-between">
                        <x-button icon="plus" position="left" wire:click="addAdminItem({{ $blockIndex }})">
                            Add to Administrative Supplies
                        </x-button>
                    </div>
                </div>
            </details>

            {{-- Admin Items Table --}}
            @if (!empty($block['administrative_items']))
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-3">
                        <h5 class="font-medium text-sm mb-0">
                            Items ({{ count($block['administrative_items']) }})
                        </h5>
                        <p class="text-sm font-semibold text-gray-700">
                            Admin Subtotal:
                            ₱{{ number_format($adminJanitorialForm->getGroupTotal($blockIndex, 'administrative_items'), 2) }}
                        </p>
                    </div>

                    <div class="overflow-x-auto border rounded-lg bg-white">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100 text-gray-700">
                                <tr>
                                    <th class="text-left px-3 py-2">Item Name</th>
                                    <th class="text-left px-3 py-2">Qty</th>
                                    <th class="text-left px-3 py-2">Unit</th>
                                    <th class="text-right px-3 py-2">Unit Cost</th>
                                    <th class="text-right px-3 py-2">Est. Cost</th>
                                    <th class="text-right px-3 py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($block['administrative_items'] as $itemIndex => $item)
                                    @php
                                        $isEditing =
                                            $adminJanitorialForm->editing['group'] === 'administrative_items' &&
                                            $adminJanitorialForm->editing['blockIndex'] === $blockIndex &&
                                            $adminJanitorialForm->editing['itemIndex'] === $itemIndex;
                                    @endphp

                                    @if (!$isEditing)
                                        <tr>
                                            <td class="px-3 py-2 font-medium">{{ $item['item_name'] ?? 'N/A' }}</td>
                                            <td class="px-3 py-2">{{ $item['quantity'] ?? 0 }}</td>
                                            <td class="px-3 py-2">{{ $item['unit'] ?? '' }}</td>
                                            <td class="px-3 py-2 text-right">
                                                ₱{{ number_format($item['estimated_unit_cost'] ?? 0, 2) }}</td>
                                            <td class="px-3 py-2 text-right font-semibold">
                                                ₱{{ number_format(($item['quantity'] ?? 0) * ($item['estimated_unit_cost'] ?? 0), 2) }}
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="flex justify-end gap-2">
                                                    <x-button sm icon="pencil"
                                                        wire:click="startEditAdminJanitorialItem({{ $blockIndex }}, {{ $itemIndex }}, 'administrative_items')">
                                                        Edit
                                                    </x-button>
                                                    <x-button flat negative icon="trash"
                                                        wire:click="removeAdminJanitorialItem({{ $blockIndex }}, {{ $itemIndex }}, 'administrative_items')" />
                                                </div>
                                            </td>
                                        </tr>
                                    @else
                                        <tr class="bg-yellow-50">
                                            <td class="px-3 py-3" colspan="6">
                                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                                    <div class="col-span-2">
                                                        <x-input wire:model="adminJanitorialForm.editForm.item_name"
                                                            label="Item Name *" />
                                                    </div>

                                                    <x-number wire:model="adminJanitorialForm.editForm.quantity"
                                                        label="Quantity *" />

                                                    <x-select.styled wire:model="adminJanitorialForm.editForm.unit"
                                                        label="Unit *" :options="[
                                                            ['label' => 'Pc', 'value' => 'pc'],
                                                            ['label' => 'Pack', 'value' => 'pack'],
                                                            ['label' => 'Box', 'value' => 'box'],
                                                            ['label' => 'Set', 'value' => 'set'],
                                                            ['label' => 'Unit', 'value' => 'unit'],
                                                            ['label' => 'Ream', 'value' => 'ream'],
                                                            ['label' => 'Bottle', 'value' => 'bottle'],
                                                            ['label' => 'Can', 'value' => 'can'],
                                                            ['label' => 'Roll', 'value' => 'roll'],
                                                            ['label' => 'Liter', 'value' => 'liter'],
                                                            ['label' => 'Kg', 'value' => 'kg'],
                                                        ]" />

                                                    <x-number
                                                        wire:model="adminJanitorialForm.editForm.estimated_unit_cost"
                                                        label="Estimated Unit Cost *" prefix="₱" step="0.01" />

                                                    <div class="col-span-2 flex items-end justify-between">
                                                        <p class="text-sm font-semibold">
                                                            Edited Total:
                                                            ₱{{ number_format(($adminJanitorialForm->editForm['quantity'] ?? 0) * ($adminJanitorialForm->editForm['estimated_unit_cost'] ?? 0), 2) }}
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
        </div>

        <hr class="my-4 border-gray-200" />

        {{-- ============ JANITORIAL SUPPLIES ============ --}}
        <div class="mb-4">
            <h4 class="font-semibold text-sm text-gray-700 mb-3">Janitorial Supplies</h4>

            {{-- Add Janitorial Item Form --}}
            <details class="mb-4 p-4 border rounded bg-white" open>
                <summary class="cursor-pointer font-medium text-sm mb-4">Add Janitorial Item</summary>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <x-input wire:model="adminJanitorialForm.currentJanitorialItem.item_name" label="Item Name *"
                            placeholder="Item description" />
                    </div>

                    <x-number wire:model="adminJanitorialForm.currentJanitorialItem.quantity" label="Quantity *" />

                    <x-select.styled wire:model="adminJanitorialForm.currentJanitorialItem.unit" label="Unit *"
                        :options="[
                            ['label' => 'Pc', 'value' => 'pc'],
                            ['label' => 'Pack', 'value' => 'pack'],
                            ['label' => 'Box', 'value' => 'box'],
                            ['label' => 'Set', 'value' => 'set'],
                            ['label' => 'Unit', 'value' => 'unit'],
                            ['label' => 'Ream', 'value' => 'ream'],
                            ['label' => 'Bottle', 'value' => 'bottle'],
                            ['label' => 'Can', 'value' => 'can'],
                            ['label' => 'Roll', 'value' => 'roll'],
                            ['label' => 'Liter', 'value' => 'liter'],
                            ['label' => 'Kg', 'value' => 'kg'],
                        ]" />

                    <x-number wire:model="adminJanitorialForm.currentJanitorialItem.estimated_unit_cost"
                        label="Estimated Unit Cost *" prefix="₱" step="0.01" />

                    <div class="col-span-3 flex items-center justify-between">
                        <x-button icon="plus" position="left"
                            wire:click="addJanitorialItem({{ $blockIndex }})">
                            Add to Janitorial Supplies
                        </x-button>
                    </div>
                </div>
            </details>

            {{-- Janitorial Items Table --}}
            @if (!empty($block['janitorial_items']))
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-3">
                        <h5 class="font-medium text-sm mb-0">
                            Items ({{ count($block['janitorial_items']) }})
                        </h5>
                        <p class="text-sm font-semibold text-gray-700">
                            Janitorial Subtotal:
                            ₱{{ number_format($adminJanitorialForm->getGroupTotal($blockIndex, 'janitorial_items'), 2) }}
                        </p>
                    </div>

                    <div class="overflow-x-auto border rounded-lg bg-white">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100 text-gray-700">
                                <tr>
                                    <th class="text-left px-3 py-2">Item Name</th>
                                    <th class="text-left px-3 py-2">Qty</th>
                                    <th class="text-left px-3 py-2">Unit</th>
                                    <th class="text-right px-3 py-2">Unit Cost</th>
                                    <th class="text-right px-3 py-2">Est. Cost</th>
                                    <th class="text-right px-3 py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($block['janitorial_items'] as $itemIndex => $item)
                                    @php
                                        $isEditing =
                                            $adminJanitorialForm->editing['group'] === 'janitorial_items' &&
                                            $adminJanitorialForm->editing['blockIndex'] === $blockIndex &&
                                            $adminJanitorialForm->editing['itemIndex'] === $itemIndex;
                                    @endphp

                                    @if (!$isEditing)
                                        <tr>
                                            <td class="px-3 py-2 font-medium">{{ $item['item_name'] ?? 'N/A' }}</td>
                                            <td class="px-3 py-2">{{ $item['quantity'] ?? 0 }}</td>
                                            <td class="px-3 py-2">{{ $item['unit'] ?? '' }}</td>
                                            <td class="px-3 py-2 text-right">
                                                ₱{{ number_format($item['estimated_unit_cost'] ?? 0, 2) }}</td>
                                            <td class="px-3 py-2 text-right font-semibold">
                                                ₱{{ number_format(($item['quantity'] ?? 0) * ($item['estimated_unit_cost'] ?? 0), 2) }}
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="flex justify-end gap-2">
                                                    <x-button sm icon="pencil"
                                                        wire:click="startEditAdminJanitorialItem({{ $blockIndex }}, {{ $itemIndex }}, 'janitorial_items')">
                                                        Edit
                                                    </x-button>
                                                    <x-button flat negative icon="trash"
                                                        wire:click="removeAdminJanitorialItem({{ $blockIndex }}, {{ $itemIndex }}, 'janitorial_items')" />
                                                </div>
                                            </td>
                                        </tr>
                                    @else
                                        <tr class="bg-yellow-50">
                                            <td class="px-3 py-3" colspan="6">
                                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                                    <div class="col-span-2">
                                                        <x-input wire:model="adminJanitorialForm.editForm.item_name"
                                                            label="Item Name *" />
                                                    </div>

                                                    <x-number wire:model="adminJanitorialForm.editForm.quantity"
                                                        label="Quantity *" />

                                                    <x-select.styled wire:model="adminJanitorialForm.editForm.unit"
                                                        label="Unit *" :options="[
                                                            ['label' => 'Pc', 'value' => 'pc'],
                                                            ['label' => 'Pack', 'value' => 'pack'],
                                                            ['label' => 'Box', 'value' => 'box'],
                                                            ['label' => 'Set', 'value' => 'set'],
                                                            ['label' => 'Unit', 'value' => 'unit'],
                                                            ['label' => 'Ream', 'value' => 'ream'],
                                                            ['label' => 'Bottle', 'value' => 'bottle'],
                                                            ['label' => 'Can', 'value' => 'can'],
                                                            ['label' => 'Roll', 'value' => 'roll'],
                                                            ['label' => 'Liter', 'value' => 'liter'],
                                                            ['label' => 'Kg', 'value' => 'kg'],
                                                        ]" />

                                                    <x-number
                                                        wire:model="adminJanitorialForm.editForm.estimated_unit_cost"
                                                        label="Estimated Unit Cost *" prefix="₱"
                                                        step="0.01" />

                                                    <div class="col-span-2 flex items-end justify-between">
                                                        <p class="text-sm font-semibold">
                                                            Edited Total:
                                                            ₱{{ number_format(($adminJanitorialForm->editForm['quantity'] ?? 0) * ($adminJanitorialForm->editForm['estimated_unit_cost'] ?? 0), 2) }}
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
        </div>

    </details>
@endforeach

{{-- Action Buttons --}}
<div class="flex flex-wrap gap-3 mt-6">
    <x-button icon="plus" wire:click="addAdminJanitorialBlock">Add Another Lot Block</x-button>
    <x-button icon="bookmark" color="blue" wire:click="saveAdminJanitorialDraft">Save Draft</x-button>
    <x-button icon="printer" flat color="green" position="left" wire:click="printPR">Print Purchase
        Request</x-button>
</div>

