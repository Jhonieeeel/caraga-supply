<div>
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Report of Supplies and Materials Issued
    </h2>
    <div class="bg-teal-100 p-3 rounded sm:my-3">
        <p class="text-sm text-teal-800">
            <span class="font-semibold text-teal-600 pr-1" aria-hidden="true">Note:</span>
            Supplies listed below are from <span class="font-semibold">completed</span>
            requisitions only.
        </p>
    </div>

    <form wire:submit.prevent="createRsmi" class="sm:pb-4 sm:pt-6 grid sm:grid-cols-3 sm:gap-4 gap-3">
        <div class="sm:col-span-2">
            <x-date range helpers wire:model.live.300ms="rsmiDate" label="Date" hint="Select your Date of Report"
                format="DD [of] MMMM [of] YYYY" />
        </div>
        <div class="sm:col-span-2">
            <x-select.styled wire:model.live.debounce.300ms="rsmiSearch" :disabled="!$this?->getSupplies" label="Supply name"
                hint="Select Supply name" :options="$this?->getSupplies" searchable />
        </div>
        <div class="col-span-2">
            <x-button text="Submit" submit />
        </div>
    </form>
    <div class="-m-1.5 overflow-x-auto">
        <div class="p-1.5 min-w-full inline-block align-middle">
            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                RIS</th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                Stock No.</th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                Supply Name</th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                Unit</th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                Requested Qty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($rsmi as $requisition)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                    {{ $requisition->ris }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                    {{ $requisition->items->first()->stock->stock_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                    {{ $requisition->items->first()->stock->supply->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                    {{ $requisition->items->first()->stock->supply->unit }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                    {{ $requisition->items->first()->requested_qty }}
                                </td>
                            </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

