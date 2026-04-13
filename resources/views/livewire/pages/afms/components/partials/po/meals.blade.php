{{-- ===================== PO MEALS (Read-Only + PO Unit Cost Edit) ===================== --}}
@if (empty($poBlocks))
    <div class="text-center py-10 text-gray-400 text-sm">
        No meal data found for this Purchase Request. Please enter meal data on the PR form first.
    </div>
@endif
@foreach ($poBlocks as $blockIndex => $block)
    <details class="border rounded-lg p-4 mb-6 bg-gray-50" open>
        <summary class="flex justify-between items-center cursor-pointer mb-4">
            <div>
                <h3 class="text-lg font-semibold">Lot Block {{ $blockIndex + 1 }}</h3>
                <p class="text-sm text-gray-600">
                    PO Total: ₱{{ number_format($this->getBlockTotal($blockIndex), 2) }}
                </p>
            </div>
            {{-- No remove button on PO --}}
        </summary>
        {{-- Main Information (Read-Only) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <p class="text-xs text-gray-500 mb-1">Lot Title</p>
                <p class="text-sm font-medium text-gray-800 bg-white border border-gray-200 rounded px-3 py-2">
                    {{ $block['lot_title'] ?: '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Location</p>
                <p class="text-sm font-medium text-gray-800 bg-white border border-gray-200 rounded px-3 py-2">
                    {{ $block['location'] ?: '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Date</p>
                <p class="text-sm font-medium text-gray-800 bg-white border border-gray-200 rounded px-3 py-2">
                    {{ $block['date'] ?: '—' }}
                </p>
            </div>
        </div>
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
                                <th class="text-right px-3 py-2">PR Unit Cost</th>
                                <th class="text-right px-3 py-2">PO Unit Cost</th>
                                <th class="text-right px-3 py-2">PO Total</th>
                                <th class="text-right px-3 py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($block['items'] as $itemIndex => $item)
                                @php
                                    $isEditing =
                                        $editingPoType === 'lot' &&
                                        $editingPoBlockIndex === $blockIndex &&
                                        $editingPoItemIndex === $itemIndex;
                                    $poUnitCost = $item['po_estimated_unit_cost'] ?? null;
                                    $displayPoUnitCost = $poUnitCost ?? $item['estimated_unit_cost'] ?? 0;
                                    $poTotal = ($item['qty'] ?? 0) * $displayPoUnitCost;
                                @endphp
                                @if (!$isEditing)
                                    <tr class="{{ $poUnitCost !== null ? '' : 'bg-yellow-50' }}">
                                        <td class="px-3 py-2 font-medium">{{ $item['mealSnack'] ?? 'N/A' }}</td>
                                        <td class="px-3 py-2 text-gray-600 truncate max-w-[220px]">{{ $item['menu'] ?? 'N/A' }}</td>
                                        <td class="px-3 py-2">{{ $item['pax_qty'] ?? 0 }}</td>
                                        <td class="px-3 py-2">{{ $item['qty'] ?? 0 }}</td>
                                        <td class="px-3 py-2">{{ $item['unit'] ?? '' }}</td>
                                        <td class="px-3 py-2 text-right text-gray-500">
                                            ₱{{ number_format($item['estimated_unit_cost'] ?? 0, 2) }}
                                        </td>
                                        <td class="px-3 py-2 text-right font-medium {{ $poUnitCost !== null ? 'text-blue-700' : 'text-yellow-600' }}">
                                            @if ($poUnitCost !== null)
                                                ₱{{ number_format($poUnitCost, 2) }}
                                            @else
                                                <span class="text-xs italic text-yellow-500">Not set</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-right font-semibold">
                                            ₱{{ number_format($poTotal, 2) }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <x-button sm icon="pencil"
                                                wire:click="startEditPoLotItem({{ $blockIndex }}, {{ $itemIndex }})">
                                                Edit PO Cost
                                            </x-button>
                                        </td>
                                    </tr>
                                @else
                                    <tr class="bg-blue-50">
                                        <td class="px-3 py-2 font-medium">{{ $item['mealSnack'] ?? 'N/A' }}</td>
                                        <td class="px-3 py-2 text-gray-600 truncate max-w-[220px]">{{ $item['menu'] ?? 'N/A' }}</td>
                                        <td class="px-3 py-2">{{ $item['pax_qty'] ?? 0 }}</td>
                                        <td class="px-3 py-2">{{ $item['qty'] ?? 0 }}</td>
                                        <td class="px-3 py-2">{{ $item['unit'] ?? '' }}</td>
                                        <td class="px-3 py-2 text-right text-gray-500">
                                            ₱{{ number_format($item['estimated_unit_cost'] ?? 0, 2) }}
                                        </td>
                                        <td class="px-3 py-2" colspan="3">
                                            <div class="flex items-center gap-2">
                                                <x-number wire:model="editingPoValue"
                                                    label="PO Unit Cost *" prefix="₱" step="0.01"
                                                    class="w-48" />
                                                <div class="flex gap-2 mt-5">
                                                    <x-button sm color="green" icon="check"
                                                        wire:click="savePoEdit">Save</x-button>
                                                    <x-button sm flat icon="x-mark"
                                                        wire:click="cancelPoEdit">Cancel</x-button>
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
        {{-- Accommodation Blocks (Read-Only) --}}
        @if (!empty($block['accommodations']))
            <div class="mt-6">
                <h4 class="text-md font-semibold mb-4">Accommodation Blocks</h4>
                @foreach ($block['accommodations'] as $accommodationIndex => $accommodation)
                    <details class="border rounded-lg p-4 mb-4 bg-blue-50" open>
                        <summary class="flex justify-between items-center cursor-pointer mb-4">
                            <h5 class="font-medium">Accommodation Block {{ $accommodationIndex + 1 }}</h5>
                            {{-- No remove button on PO --}}
                        </summary>
                        {{-- Accommodation Main Information (Read-Only) --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Accommodation Title</p>
                                <p class="text-sm font-medium text-gray-800 bg-white border border-gray-200 rounded px-3 py-2">
                                    {{ $accommodation['accommodation_title'] ?: '—' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Location</p>
                                <p class="text-sm font-medium text-gray-800 bg-white border border-gray-200 rounded px-3 py-2">
                                    {{ $accommodation['location'] ?: '—' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Date</p>
                                <p class="text-sm font-medium text-gray-800 bg-white border border-gray-200 rounded px-3 py-2">
                                    {{ $accommodation['date'] ?: '—' }}
                                </p>
                            </div>
                        </div>
                        {{-- Accommodation Items --}}
                        @if (!empty($accommodation['items']))
                            <div class="mb-4">
                                <h6 class="text-sm font-medium mb-2">
                                    Accommodation Items ({{ count($accommodation['items']) }})
                                </h6>
                                <div class="overflow-x-auto border rounded-lg bg-white">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-gray-100 text-gray-700">
                                            <tr>
                                                <th class="text-left px-3 py-2">Room Req.</th>
                                                <th class="text-left px-3 py-2">Pax</th>
                                                <th class="text-left px-3 py-2">Rooms</th>
                                                <th class="text-left px-3 py-2">Nights</th>
                                                <th class="text-left px-3 py-2">Qty</th>
                                                <th class="text-left px-3 py-2">Unit</th>
                                                <th class="text-right px-3 py-2">PR Unit Cost</th>
                                                <th class="text-right px-3 py-2">PO Unit Cost</th>
                                                <th class="text-right px-3 py-2">PO Total</th>
                                                <th class="text-right px-3 py-2">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y">
                                            @foreach ($accommodation['items'] as $itemIndex => $item)
                                                @php
                                                    $isEditing =
                                                        $editingPoType === 'accommodation' &&
                                                        $editingPoBlockIndex === $blockIndex &&
                                                        $editingPoAccommodationIndex === $accommodationIndex &&
                                                        $editingPoItemIndex === $itemIndex;
                                                    $poUnitCost = $item['po_estimated_unit_cost'] ?? null;
                                                    $displayPoUnitCost = $poUnitCost ?? $item['estimated_unit_cost'] ?? 0;
                                                    $poTotal = ($item['qty'] ?? 0) * $displayPoUnitCost;
                                                @endphp
                                                @if (!$isEditing)
                                                    <tr class="{{ $poUnitCost !== null ? '' : 'bg-yellow-50' }}">
                                                        <td class="px-3 py-2 font-medium">{{ $item['room_requirement'] ?? 'N/A' }}</td>
                                                        <td class="px-3 py-2">{{ $item['no_of_pax'] ?? 0 }}</td>
                                                        <td class="px-3 py-2">{{ $item['no_of_rooms'] ?? 0 }}</td>
                                                        <td class="px-3 py-2">{{ $item['no_of_nights'] ?? 0 }}</td>
                                                        <td class="px-3 py-2">{{ $item['qty'] ?? 0 }}</td>
                                                        <td class="px-3 py-2">{{ $item['unit'] ?? '' }}</td>
                                                        <td class="px-3 py-2 text-right text-gray-500">
                                                            ₱{{ number_format($item['estimated_unit_cost'] ?? 0, 2) }}
                                                        </td>
                                                        <td class="px-3 py-2 text-right font-medium {{ $poUnitCost !== null ? 'text-blue-700' : 'text-yellow-600' }}">
                                                            @if ($poUnitCost !== null)
                                                                ₱{{ number_format($poUnitCost, 2) }}
                                                            @else
                                                                <span class="text-xs italic text-yellow-500">Not set</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-2 text-right font-semibold">
                                                            ₱{{ number_format($poTotal, 2) }}
                                                        </td>
                                                        <td class="px-3 py-2 text-right">
                                                            <x-button sm icon="pencil"
                                                                wire:click="startEditPoAccommodationItem({{ $blockIndex }}, {{ $accommodationIndex }}, {{ $itemIndex }})">
                                                                Edit PO Cost
                                                            </x-button>
                                                        </td>
                                                    </tr>
                                                @else
                                                    <tr class="bg-blue-100">
                                                        <td class="px-3 py-2 font-medium">{{ $item['room_requirement'] ?? 'N/A' }}</td>
                                                        <td class="px-3 py-2">{{ $item['no_of_pax'] ?? 0 }}</td>
                                                        <td class="px-3 py-2">{{ $item['no_of_rooms'] ?? 0 }}</td>
                                                        <td class="px-3 py-2">{{ $item['no_of_nights'] ?? 0 }}</td>
                                                        <td class="px-3 py-2">{{ $item['qty'] ?? 0 }}</td>
                                                        <td class="px-3 py-2">{{ $item['unit'] ?? '' }}</td>
                                                        <td class="px-3 py-2 text-right text-gray-500">
                                                            ₱{{ number_format($item['estimated_unit_cost'] ?? 0, 2) }}
                                                        </td>
                                                        <td class="px-3 py-2" colspan="3">
                                                            <div class="flex items-center gap-2">
                                                                <x-number wire:model="editingPoValue"
                                                                    label="PO Unit Cost *" prefix="₱" step="0.01"
                                                                    class="w-48" />
                                                                <div class="flex gap-2 mt-5">
                                                                    <x-button sm color="green" icon="check"
                                                                        wire:click="savePoEdit">Save</x-button>
                                                                    <x-button sm flat icon="x-mark"
                                                                        wire:click="cancelPoEdit">Cancel</x-button>
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
            </div>
        @endif
    </details>
@endforeach