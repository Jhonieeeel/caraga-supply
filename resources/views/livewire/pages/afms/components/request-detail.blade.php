<div>
    @if ($requisition)
        @php
            $isApproved =
                $requisition->requested_by &&
                $requisition->approved_by &&
                $requisition->issued_by &&
                $requisition->received_by;

            $status = auth()->user()->id === $requisition->user_id || auth()->user()->hasRole('Super Admin');

            $disableStatus = $isApproved || !$status;

            $isCompleted = $requisition->completed;

            $isOwner = auth()->user()->id === $requisition->user_id;
            $isUser = auth()->user()->hasRole('User');
            $isAdmin = auth()->user()->hasRole('Super Admin');

            $twoFieldsFilled = $requisition->requested_by && $requisition->received_by;

            // disable if two fields are filled AND you are NOT admin
            $shouldDisable = $twoFieldsFilled && !$isAdmin;
        @endphp
        <div class="sm:flex items-center gap-x-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Requests Detail
            </h2>
            @if ($requisition->completed)
                <x-badge text="Completed" color="green" light />
            @elseif($isApproved)
                <x-badge text="Approved" color="green" light />
            @endif
        </div>
        @if (!$requisition->completed)
            @if ($isApproved)
                <div class="bg-teal-100 p-3 rounded sm:my-3">
                    <p class="text-sm text-teal-800">
                        <span class="font-semibold text-teal-600 pr-1" aria-hidden="true">Ready:</span>
                        All required fields are completed. You can now generate the RIS copy.
                    </p>
                </div>
            @else
                <div class="bg-orange-100 p-3 rounded sm:my-3">
                    <p class="text-sm text-orange-800">
                        <span class="font-semibold text-orange-600 pr-1" aria-hidden="true">Note:</span>
                        Please complete all required fields to enable RIS copy generation.
                    </p>
                </div>
            @endif
        @endif

        <div class="sm:py-4 py-3">
            <form wire:submit.prevent="update" class="w-full grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="col-span-4 sm:flex items-center justify-between w-full gap-3.5">
                    <div class="flex-1">
                        <x-input :disabled="$disableStatus" wire:model="requestForm.ris" label="RIS" hint="Input RIS"
                            class="w-full" />
                    </div>
                    <div>
                        @if (!$isApproved && (auth()->user()->id === $requisition->user_id || auth()->user()->hasRole('Super Admin')))
                            <x-button wire:click="generateRIS" loading="generateRIS" class="w-full sm:w-auto"
                                md>Generate
                            </x-button>
                        @endif
                    </div>
                </div>
                {{-- users --}}
                <div class="col-span-4">
                    <h3 class="text-lg font-semibold">Users </h3>
                </div>
                <div class="sm:col-span-2 col-span-4">
                    <x-select.styled :disabled="$isCompleted" wire:model="requestForm.requested_by" label="Requested by *"
                        hint="Select requester" :options="$this->getUsers" searchable />
                </div>
                <div class="sm:col-span-2 col-span-4">
                    <x-select.styled :disabled="$isCompleted" wire:model="requestForm.received_by" label="Received by *"
                        hint="Select receiver" :options="$this->getUsers" searchable />
                </div>
                {{-- dates --}}

                @if ($isAdmin)
                    <div class="sm:col-span-2 col-span-4">
                        <x-select.styled :disabled="$isCompleted" wire:model="requestForm.approved_by" label="Approved by *"
                            hint="Select approver" :options="$this->getUsers" searchable />
                    </div>
                    <div class="sm:col-span-2 col-span-4">
                        <x-select.styled :disabled="$isCompleted" wire:model="requestForm.issued_by" label="Issued by *"
                            hint="Select issueance" :options="$this->getUsers" searchable />
                    </div>
                    <div class="col-span-4">
                        <h3 class="text-lg font-semibold">Dates</h3>
                    </div>
                    <div class="sm:col-span-2 col-span-4">
                        <x-date format="YYYY-MM-DD" label="Approved Date *" wire:model="requestForm.approved_date" />
                    </div>
                    <div class="sm:col-span-2 col-span-4">
                        <x-date format="YYYY-MM-DD" label="Issued Date *" wire:model="requestForm.issued_date" />
                    </div>
                @endif
                <div class="sm:col-span-2 col-span-4">
                    <x-date format="YYYY-MM-DD" label="Requested Date *" wire:model="requestForm.requested_date" />
                </div>

                <div class="sm:col-span-2 col-span-4">
                    <x-date format="YYYY-MM-DD" label="Received Date *" wire:model="requestForm.received_date" />
                </div>
                <div class="col-span-4">
                    <x-textarea label="Purpose *" wire:model="requestForm.purpose" hint="Insert the purpose" />
                </div>
                <div class="sm:col-span-4 col-span-4 sm:ms-auto flex sm:items-center gap-x-3">
                    @if (!$requisition->completed)
                        @if (!$requisition->pdf)
                            @if (!$isApproved)
                                @if ($isAdmin || ($isOwner && !$shouldDisable))
                                    <x-button submit loading="update" icon="document" position="right">
                                        {{ $isOwner ? 'Submit' : 'Update' }}
                                    </x-button>
                                @elseif ($isOwner && $shouldDisable)
                                    <x-button icon="document" position="right" :disabled="$shouldDisable">
                                        Pending
                                    </x-button>
                                @endif
                            @else
                                <x-button wire:click="approvedRequisition({{ $requisition->id }})" loading="approved"
                                    icon="check" position="right" color="teal" :disabled="$shouldDisable">
                                    Approved
                                </x-button>
                            @endif
                        @endif
                    @else
                        <x-button wire:click="viewPdf" icon="document" position="right">
                            View PDF
                        </x-button>
                    @endif
                </div>
            </form>
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
                                            Stock No.</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                            Supply Name</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                            Requested Quantity</th>
                                        @if (!$isApproved && (auth()->user()->id === $requisition->user_id || auth()->user()->hasRole('Super Admin')))
                                            <th scope="col"
                                                class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                                Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($requisition->items as $item)
                                        <tr>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 capitalize">
                                                {{ $item->stock->stock_number }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                                {{ $item->stock->supply->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                                {{ $item->requested_qty }}
                                            </td>
                                            @if (!$isApproved && (auth()->user()->id === $requisition->user_id || auth()->user()->hasRole('Super Admin')))
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                                    <x-button.circle flat
                                                        wire:click="editRequestItem({{ $item }})"
                                                        icon="pencil" color="teal" />
                                                    <x-button.circle flat
                                                        wire:click="deleteRequisitionItem({{ $item->id }})"
                                                        icon="trash" color="red" />
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <small class="text-sm text-gray-500">No Selected Requisition Yet</small>
        <button class="text-sm block cursor-pointer underline" wire:click="$set('tab', 'Requests List')">Go
            back</button>
    @endif

    {{-- edit modal --}}
    <x-modal title="Edit Stock" id="edit-item" size="sm">
        <form wire:submit.prevent="updateRequestItem">
            <div><x-number hint="Press the plus button to increase one by one"
                    label="{{ $requisitionItem?->stock->supply->name }}" wire:model="itemForm.requested_qty" />
            </div>
            <div class="sm:pt-4 py-2 flex justify-end items-center">
                <x-button submit icon="hashtag" wire:target='updateRequestItem' position="right">Update</x-button>
            </div>
        </form>
    </x-modal>
</div>

