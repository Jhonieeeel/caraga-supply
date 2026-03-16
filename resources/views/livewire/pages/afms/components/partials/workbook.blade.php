{{-- ===================== WORKBOOK ===================== --}}
@foreach ($workbookForm->blocks as $blockIndex => $block)
    <details class="border rounded-lg p-4 mb-6 bg-gray-50" open>
        <summary class="flex justify-between items-center cursor-pointer mb-4">
            <div>
                <h3 class="text-lg font-semibold">Workbook Block {{ $blockIndex + 1 }}</h3>
                <p class="text-sm text-gray-600">
                    Total: ₱{{ number_format($this->getBlockTotal($blockIndex), 2) }}
                </p>
            </div>
            @if ($blockIndex > 0)
                <x-button flat negative icon="trash" wire:click="removeBlock({{ $blockIndex }})" />
            @endif
        </summary>

        {{-- Block Title --}}
        <div class="grid grid-cols-1 gap-4 mb-6">
            <x-input wire:model="workbookForm.blocks.{{ $blockIndex }}.block_title" label="Block Title *"
                placeholder="e.g., Office Supplies, Equipment, etc." />
        </div>

        {{-- Add New Workbook Item --}}
        <details class="mb-6 p-4 border rounded bg-white" open>
            <summary class="cursor-pointer font-medium text-sm mb-4">Add New Workbook Item</summary>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="col-span-2">
                    <x-input wire:model="workbookForm.currentWorkbookItem.particular" label="Particular *"
                        placeholder="Item description" />
                </div>

                <x-input wire:model="workbookForm.currentWorkbookItem.delivery_date" label="Date/Time of Delivery *"
                    placeholder="e.g., July 23, 2025 10:00 AM" />

                <x-number wire:model="workbookForm.currentWorkbookItem.qty" label="Quantity *" />
                <x-select.styled wire:model="workbookForm.currentWorkbookItem.unit" label="Unit *" :options="[
                    ['label' => 'Pc', 'value' => 'pc'],
                    ['label' => 'Pack', 'value' => 'pack'],
                    ['label' => 'Box', 'value' => 'box'],
                    ['label' => 'Set', 'value' => 'set'],
                    ['label' => 'Unit', 'value' => 'unit'],
                    ['label' => 'Ream', 'value' => 'ream'],
                    ['label' => 'Meter', 'value' => 'meter'],
                    ['label' => 'Kg', 'value' => 'kg'],
                    ['label' => 'Liter', 'value' => 'liter'],
                    ['label' => 'Bottle', 'value' => 'bottle'],
                    ['label' => 'Can', 'value' => 'can'],
                    ['label' => 'Roll', 'value' => 'roll'],
                ]" />

                <x-number wire:model="workbookForm.currentWorkbookItem.estimated_unit_cost"
                    label="Estimated Unit Cost *" prefix="₱" step="0.01" />

                <div class="col-span-3 flex items-center justify-between">
                    <x-button icon="plus" position="left" wire:click="addWorkbookItem({{ $blockIndex }})">
                        Add to Workbook Block {{ $blockIndex + 1 }}
                    </x-button>
                </div>
            </div>
        </details>

        {{-- Workbook Items Table --}}
        @if (!empty($block['items']))
            <div class="mb-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-medium text-sm mb-0">Workbook Items ({{ count($block['items']) }})</h4>
                    <p class="text-sm font-semibold text-gray-700">
                        Items Subtotal: ₱{{ number_format($this->sumItemsPublic($block['items']), 2) }}
                    </p>
                </div>

                <div class="overflow-x-auto border rounded-lg bg-white">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="text-left px-3 py-2">Particular</th>
                                <th class="text-left px-3 py-2">Delivery</th>
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
                                        $workbookForm->editing['type'] === 'workbook' &&
                                        $workbookForm->editing['blockIndex'] === $blockIndex &&
                                        $workbookForm->editing['itemIndex'] === $itemIndex;
                                @endphp

                                @if (!$isEditing)
                                    <tr>
                                        <td class="px-3 py-2 font-medium">{{ $item['particular'] ?? 'N/A' }}</td>
                                        <td class="px-3 py-2 text-gray-600">{{ $item['delivery_date'] ?? 'N/A' }}</td>
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
                                                    wire:click="startEditWorkbookItem({{ $blockIndex }}, {{ $itemIndex }})">
                                                    Edit
                                                </x-button>
                                                <x-button flat negative icon="trash"
                                                    wire:click="removeItem({{ $blockIndex }}, {{ $itemIndex }}, 'workbook')" />
                                            </div>
                                        </td>
                                    </tr>
                                @else
                                    <tr class="bg-yellow-50">
                                        <td class="px-3 py-3" colspan="7">
                                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                                <div class="col-span-2">
                                                    <x-input wire:model="workbookForm.editForm.particular"
                                                        label="Particular *" />
                                                </div>

                                                <x-input wire:model="workbookForm.editForm.delivery_date"
                                                    label="Date/Time of Delivery *" />

                                                <x-number wire:model="workbookForm.editForm.qty" label="Quantity *" />
                                                <x-select.styled wire:model="workbookForm.editForm.unit" label="Unit *"
                                                    :options="[
                                                        ['label' => 'Pc', 'value' => 'pc'],
                                                        ['label' => 'Pack', 'value' => 'pack'],
                                                        ['label' => 'Box', 'value' => 'box'],
                                                        ['label' => 'Set', 'value' => 'set'],
                                                        ['label' => 'Unit', 'value' => 'unit'],
                                                        ['label' => 'Ream', 'value' => 'ream'],
                                                        ['label' => 'Meter', 'value' => 'meter'],
                                                        ['label' => 'Kg', 'value' => 'kg'],
                                                        ['label' => 'Liter', 'value' => 'liter'],
                                                        ['label' => 'Bottle', 'value' => 'bottle'],
                                                        ['label' => 'Can', 'value' => 'can'],
                                                        ['label' => 'Roll', 'value' => 'roll'],
                                                    ]" />

                                                <x-number wire:model="workbookForm.editForm.estimated_unit_cost"
                                                    label="Estimated Unit Cost *" prefix="₱" step="0.01" />

                                                <div class="col-span-2 flex items-end justify-between">
                                                    <p class="text-sm font-semibold">
                                                        Edited Total:
                                                        ₱{{ number_format(($workbookForm->editForm['qty'] ?? 0) * ($workbookForm->editForm['estimated_unit_cost'] ?? 0), 2) }}
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

    </details>
@endforeach

{{-- Action Buttons --}}
<div class="flex flex-wrap gap-3 mt-6">
    <x-button icon="plus" wire:click="addWorkbookBlock">Add Another Workbook Block</x-button>
    <x-button icon="bookmark" color="blue" wire:click="saveWorkbookDraft">Save Draft</x-button>
    <x-button icon="printer" flat color="green" position="left" wire:click="printPR">Print Purchase Request</x-button>
</div>

