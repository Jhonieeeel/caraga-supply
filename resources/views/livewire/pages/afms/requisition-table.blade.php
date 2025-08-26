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
                            <x-table :headers="$this->requestHeaders" :rows="$this->requestRows" filter
                              :quantity="[3, 5, 10]" loading paginate>
                              @interact('column_action', $requisition)
                                  <x-button.circle color="teal" icon="magnifying-glass" wire:click="viewRequisition({{ $requisition }})" />
                                  <x-button.circle color="red" icon="trash" wire:click="deleteRequisition({{ $requisition->id }})" />
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
                            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                                Requests Detail
                            </h2>
                            <div class="sm:py-4 py-3">
                                <form wire:submit.prevent="update" class="w-full grid grid-cols-2 sm:grid-cols-4 gap-4">
                                   <div class="col-span-4 sm:flex items-center gap-3.5">
                                      <div class="flex-1">
                                          <x-input wire:model="requestForm.ris" label="RIS" hint="Input RIS" class="w-full" />
                                      </div>
                                      <div>
                                          <x-button wire:click="generateRIS" loading class="w-full sm:w-auto" md>Generate RIS</x-button>
                                      </div>
                                  </div>
                                    <div class="sm:col-span-2 col-span-4">
                                      <x-select.styled wire:model="requestForm.requested_by" label="Requested by *" hint="Select requester"  :options="$this->getUsers" searchable />
                                    </div>
                                    <div class="sm:col-span-2 col-span-4">
                                      <x-select.styled wire:model="requestForm.approved_by" label="Approved by *" hint="Select approver"  :options="$this->getUsers" searchable />
                                    </div>
                                    <div class="sm:col-span-2 col-span-4">
                                      <x-select.styled wire:model="requestForm.issued_by" label="Issued by *" hint="Select issuenace"  :options="$this->getUsers" searchable />
                                    </div>
                                    <div class="sm:col-span-2 col-span-4">
                                      <x-select.styled wire:model="requestForm.received_by" label="Received by *" hint="Select receiver"  :options="$this->getUsers" searchable />
                                    </div>
                                    <div class="sm:col-span-4 col-span-4 sm:ms-auto">
                                        <x-button submit loading icon="document" position="right">Submit</x-button>
                                    </div>
                                  </form>
                            </div>
                          @else
                            <small class="text-sm text-gray-500">No Selected Requisition Yet</small>
                          @endif
                        </div>
                    </x-tab.items>
                    <x-tab.items tab="RIS">
                        <x-slot:right>
                            <x-icon name="document" class="w-5 h-5" />
                        </x-slot:right>
                        <div class="px-4">
                            <div>
                                <x-step wire:model="step"
                                        panels 
                                        previous
                                        >
                                    <x-step.items step="1"
                                                  title="Pending Approval"
                                                  description="Awaiting admin approval."
                                                  completed="{{ $requisition?->completed }}"
                                                  >
                                        <small class="text-sm py-6 text-center">Please wait for the admin approval</small>    
                                        <div class="flex justify-end w-full">
                                          <x-button 
                                            wire:click="$set('step', 2)" 
                                            :disabled="$requisition?->completed">
                                            Next
                                        </x-button>
                                        </div>                            
                                    </x-step.items>
                                    <x-step.items step="2"
                                                  title="Ready for Printing"
                                                  description="Generate the document.">
                                        <small class="text-sm py-6 text-center">You can now print the generated RIS.</small>
                                        <div class="flex justify-end w-full">
                                          <x-button 
                                            wire:click="$set('step', 2)" 
                                            :disabled="$requisition?->completed">
                                            Next
                                        </x-button>
                                        </div>
                                    </x-step.items>
                                    <x-step.items step="3"
                                                  title="Completed – Signed and Filed"
                                                  description="Upload the signed document">
                                        <small class="text-sm py-6 text-center">File Updated.</small>
                                        <div class="flex justify-end w-full">
                                          <x-button 
                                            wire:click="$set('step', 2)" 
                                            :disabled="$requisition?->completed">
                                            Next
                                        </x-button>
                                        </div>
                                    </x-step.items>
                                    
                                </x-step>
                            </div>
                        </div>
                    </x-tab.items>
                </x-tab>
            </div>
        </div>

        {{-- add request --}}
        <x-modal title="Stocks Available" id="add-request" size="3xl">
            <p class="text-sm text-gray-500">Select a stock then add your quantity.</p>
            <form wire:submit.prevent='create'>
                <x-table  :$headers :rows="$this->rows" filter
                    :quantity="[3, 5, 10]" loading paginate>
                    @interact('column_action', $stock)
                        <x-input type='number' min='1' max="{{ $stock->quantity }}"
                            wire:model="itemForm.requestedItems.{{ $stock->id }}"  />
                    @endinteract
                </x-table>
                <div class="sm:pt-4 py-2 flex justify-end items-center">
                    <x-button submit icon="cube" position="right">Submit</x-button>
                </div>
            </form>
        </x-modal>

    </div>
</div>


{{-- 
<div class="flex flex-col">
  <div class="-m-1.5 overflow-x-auto">
    <div class="p-1.5 min-w-full inline-block align-middle">
      <div class="border border-gray-200 rounded-lg divide-y divide-gray-200">
        <div class="py-3 px-4">
          <div class="relative max-w-xs">
            <label for="hs-table-search" class="sr-only">Search</label>
            <input type="text" name="hs-table-search" id="hs-table-search" class="py-1.5 sm:py-2 px-3 ps-9 block w-full border-gray-200 shadow-2xs rounded-lg sm:text-sm focus:z-10 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none" placeholder="Search for items">
            <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3">
              <svg class="size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.3-4.3"></path>
              </svg>
            </div>
          </div>
        </div>
        <div class="overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="py-3 px-4 pe-0">
                  <div class="flex items-center h-5">
                    <input id="hs-table-search-checkbox-all" type="checkbox" class="border-gray-200 rounded-sm text-blue-600 focus:ring-blue-500">
                    <label for="hs-table-search-checkbox-all" class="sr-only">Checkbox</label>
                  </div>
                </th>
                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">Name</th>
                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">Age</th>
                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">Address</th>
                <th scope="col" class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">John Brown</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">45</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">New York No. 1 Lake Park</td>
                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                  <button type="button" class="inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent text-blue-600 hover:text-blue-800 focus:outline-hidden focus:text-blue-800 disabled:opacity-50 disabled:pointer-events-none">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div> --}}