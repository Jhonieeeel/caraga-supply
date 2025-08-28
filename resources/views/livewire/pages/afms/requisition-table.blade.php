<div>
    <div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-6">
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
                    <x-tab.items tab="Requests List">
                        <x-slot:right>
                            <x-icon name="list-bullet" class="w-5 h-5" />
                        </x-slot:right>
                        <div>
                            <x-table :headers="$this->requestHeaders" :rows="$this->requestRows" filter :quantity="[3, 5, 10]" loading paginate>
                                @interact('column_completed', $requisition)
                                    @if ($requisition->completed)
                                        <x-badge text="Completed" color="green" outline />
                                    @else
                                        <x-badge text="Pending" color="red" outline />
                                    @endif
                                @endinteract
                                @interact('column_action', $requisition)
                                    <x-button.circle color="teal" icon="magnifying-glass"
                                        wire:click="viewRequisition({{ $requisition }})" />
                                    <x-button.circle color="red" icon="trash"
                                        wire:click="deleteRequisition({{ $requisition->id }})" />
                                @endinteract
                            </x-table>
                        </div>
                    </x-tab.items>
                    <x-tab.items tab="Requests Detail">
                        <x-slot:right>
                            <x-icon name="magnifying-glass" class="w-5 h-5" />
                        </x-slot:right>
                        <div>
                            @if ($requisition)
                                @php
                                    $isApproved =
                                        $requisition->requested_by &&
                                        $requisition->approved_by &&
                                        $requisition->issued_by &&
                                        $requisition->received_by;
                                @endphp
                                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                                    Requests Detail
                                </h2>
                                @if ($isApproved)
                                    <div class="bg-teal-100 p-3 rounded sm:my-3">
                                        <p class="text-sm text-teal-800">
                                            <span class="font-semibold text-teal-600 pr-1"
                                                aria-hidden="true">Ready:</span>
                                            All required fields are completed. You can now generate the RIS copy.
                                        </p>
                                    </div>
                                @else
                                    <div class="bg-orange-100 p-3 rounded sm:my-3">
                                        <p class="text-sm text-orange-800">
                                            <span class="font-semibold text-orange-600 pr-1"
                                                aria-hidden="true">Note:</span>
                                            Please complete all required fields to enable RIS copy generation.
                                        </p>
                                    </div>
                                @endif

                                <div class="sm:py-4 py-3">
                                    <form wire:submit.prevent="update"
                                        class="w-full grid grid-cols-2 sm:grid-cols-4 gap-4">
                                        <div class="col-span-4 sm:flex items-center gap-3.5">
                                            <div class="flex-1">
                                                <x-input wire:model="requestForm.ris" label="RIS" hint="Input RIS"
                                                    class="w-full" />
                                            </div>
                                            <div>
                                                <x-button wire:click="generateRIS" class="w-full sm:w-auto" md>Generate
                                                    RIS Code</x-button>
                                            </div>
                                        </div>
                                        <div class="sm:col-span-2 col-span-4">
                                            <x-select.styled wire:model="requestForm.requested_by"
                                                label="Requested by *" hint="Select requester" :options="$this->getUsers"
                                                searchable />
                                        </div>
                                        <div class="sm:col-span-2 col-span-4">
                                            <x-select.styled wire:model="requestForm.approved_by" label="Approved by *"
                                                hint="Select approver" :options="$this->getUsers" searchable />
                                        </div>
                                        <div class="sm:col-span-2 col-span-4">
                                            <x-select.styled wire:model="requestForm.issued_by" label="Issued by *"
                                                hint="Select issuenace" :options="$this->getUsers" searchable />
                                        </div>
                                        <div class="sm:col-span-2 col-span-4">
                                            <x-select.styled wire:model="requestForm.received_by" label="Received by *"
                                                hint="Select receiver" :options="$this->getUsers" searchable />
                                        </div>
                                        <div class="sm:col-span-4 col-span-4 sm:ms-auto flex sm:items-center gap-x-3">
                                            <x-button submit loading icon="document" wire:target='update'
                                                position="right">Update</x-button>
                                        </div>
                                    </form>
                                    <form>
                                        <div class="sm:col-span-4 flex flex-col sm:pt-4">
                                            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                                                Requested Items
                                            </h2>
                                            <div class="-m-1.5 overflow-x-auto">
                                                <div class="p-1.5 min-w-full inline-block align-middle">
                                                    <div class="overflow-hidden">
                                                        <table class="min-w-full divide-y divide-gray-200">
                                                            <thead>
                                                                <tr>
                                                                    <th scope="col"
                                                                        class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                                                        ID</th>
                                                                    <th scope="col"
                                                                        class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                                                        Stock No.</th>
                                                                    <th scope="col"
                                                                        class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                                                        Supply Name</th>
                                                                    <th scope="col"
                                                                        class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                                                        Requested Quantity</th>
                                                                    <th scope="col"
                                                                        class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                                                        Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-gray-200">
                                                                @foreach ($requisition->items as $item)
                                                                    <tr>
                                                                        <td
                                                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                                                            {{ $item->id }}</td>
                                                                        <td
                                                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                                                            {{ $item->stock->stock_number }}</td>
                                                                        <td
                                                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                                                            {{ $item->stock->supply->name }}</td>
                                                                        <td
                                                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                                                            {{ $item->requested_qty }}
                                                                        </td>
                                                                        <td
                                                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                                                            <x-button.circle
                                                                                wire:click="editRequestItem({{ $item }})"
                                                                                icon="pencil" color="teal" />
                                                                            <x-button.circle
                                                                                wire:click="deleteRequisitionItem({{ $item->id }})"
                                                                                icon="trash" color="red" />
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            @else
                                <small class="text-sm text-gray-500">No Selected Requisition Yet</small>
                                <button class="text-sm block cursor-pointer underline"
                                    wire:click="$set('tab', 'Requests List')">Go back</button>
                            @endif
                        </div>
                    </x-tab.items>
                    <x-tab.items tab="RIS">
                        <x-slot:right>
                            <x-icon name="document" class="w-5 h-5" />
                        </x-slot:right>
                        @if ($requisition)
                            @php
                                $isApproved =
                                    $requisition->requested_by &&
                                    $requisition->approved_by &&
                                    $requisition->issued_by &&
                                    $requisition->received_by;
                            @endphp
                            <div>
                                <x-step wire:model="step" panels>
                                    <x-step.items step="1" title="Admin Approval"
                                        description="Your request has been processed">
                                        <small
                                            class="text-sm py-6 text-center">{{ $isApproved
                                                ? "Generate the RIS and proceed to 'Next' "
                                                : 'Please wait while the requisition is being approved by all parties.' }}</small>
                                        <div class="sm:py-3">
                                            @if ($isApproved)
                                                <x-button wire:click="getRIS" loading wire:target="getRIS"
                                                    text="Generate RIS" icon="document" position="right"
                                                    color="teal" outline />
                                            @endif
                                        </div>
                                        <div class="flex justify-end w-full">
                                            <x-button wire:click="$set('step', 2), getRIS" :disabled="!$requisition?->pdf">
                                                Next
                                            </x-button>
                                        </div>
                                    </x-step.items>
                                    <x-step.items step="2" title="RIS Ready to Print"
                                        description="Proceed to print the RIS.">
                                        <small class="text-sm py-6 text-center">You can now print the generated
                                            RIS.</small>
                                        <div class="sm:py-3 py-2">
                                            <a href="{{ asset('storage/' . $requisition->pdf) }}" target="_blank"
                                                class="text-blue-600 underline">
                                                View PDF
                                            </a>
                                        </div>
                                        <div class="sm:pt-4 pt-3 flex justify-between w-full">
                                            <x-button wire:click="$set('step', 1)">
                                                Previous
                                            </x-button>
                                            <x-button wire:click="$set('step', 3)" :disabled="!$requisition?->pdf">
                                                Next
                                            </x-button>
                                        </div>
                                    </x-step.items>
                                    <x-step.items step="3" title="Upload Signed Document"
                                        description="Upload the signed RIS.">
                                        <small class="text-sm py-6 text-center">File Updated.</small>
                                        <form wire:submit.prevent="updateRIS" enctype="multipart/form-data">
                                            <div class="sm:py-4 py-2">
                                                <x-upload accept="application/pdf" wire:model="temporaryFile"
                                                    label="RIS Document" hint="Please upload RIS document."
                                                    tip="Upload our Signed RIS here" />
                                            </div>
                                            <div class="sm:pt-4 pt-3 flex justify-between w-full">
                                                <x-button wire:click="$set('step', 2)">
                                                    Previous
                                                </x-button>
                                                <x-button submit :disabled="!$this->temporaryFile">
                                                    Finish
                                                </x-button>
                                            </div>
                                        </form>
                                    </x-step.items>
                                </x-step>
                            </div>
                        @else
                            <small class="text-sm text-gray-500">No Selected Requisition Yet.</small>
                            <button class="text-sm block cursor-pointer underline"
                                wire:click="$set('tab', 'Requests List')">Go back</button>
                        @endif
                    </x-tab.items>
                    <x-tab.items tab="RSMI">
                        <x-slot:right>
                            <x-icon name="cog-6-tooth" class="w-5 h-5" />
                        </x-slot:right>
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

                            <form wire:submit.prevent="createRsmi"
                                class="sm:pb-4 sm:pt-6 grid sm:grid-cols-3 sm:gap-4 gap-3">
                                <div class="sm:col-span-2">
                                    <x-date range helpers wire:model="rsmiDate/" label="Date"
                                        hint="Select your Date of Report" format="DD [of] MMMM [of] YYYY" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-select.styled wire:model.live.debounce.300ms="rsmiSearch" label="Supply name"
                                        hint="Select Supply name" :options="$this->getSupplies" searchable />
                                </div>
                                <div class="col-span-2">
                                    <x-button text="Submit" submit />
                                </div>
                            </form>
                            @if ($rsmi)
                                <div class="-m-1.5 overflow-x-auto">
                                    <div class="p-1.5 min-w-full inline-block align-middle">
                                        <div class="overflow-hidden">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead>
                                                    <tr>
                                                        <th scope="col"
                                                            class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                                            RIS</th>
                                                        <th scope="col"
                                                            class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                                            Stock No.</th>
                                                        <th scope="col"
                                                            class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                                            Supply Name</th>
                                                        <th scope="col"
                                                            class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                                            Unit</th>
                                                        <th scope="col"
                                                            class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                                            Requested Qty</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200">
                                                    @foreach ($rsmi as $requisition)
                                                        <tr>
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                                                {{ $requisition->ris }}
                                                            </td>
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                                                {{ $requisition->items->first()->stock->stock_number }}
                                                            </td>
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                                                {{ $requisition->items->first()->stock->supply->name }}
                                                            </td>
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                                                {{ $requisition->items->first()->stock->supply->unit }}
                                                            </td>
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                                                {{ $requisition->items->first()->requested_qty }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </x-tab.items>
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
                <div class="sm:pt-4 py-2 flex justify-end items-center">
                    <x-button submit icon="cube" position="right">Submit</x-button>
                </div>
            </form>
        </x-modal>

        <x-modal title="Edit Stock" id="edit-item" size="sm">
            <form wire:submit.prevent="updateRequestItem">
                <div><x-number hint="Press the plus button to increase one by one"
                        label="{{ $requisitionItem?->stock->supply->name }}" wire:model="itemForm.requested_qty" />
                </div>
                <div class="sm:pt-4 py-2 flex justify-end items-center">
                    <x-button submit icon="hashtag" wire:target='updateRequestItem'
                        position="right">Update</x-button>
                </div>
            </form>
        </x-modal>
    </div>
</div>

