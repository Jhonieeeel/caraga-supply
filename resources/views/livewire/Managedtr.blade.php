<div class="p-6">
    <div class="bg-white shadow-md shadow-gray-200 rounded-lg p-6">
        <!-- Header with buttons -->
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold">{{ $pageTitle }}</h1>
        </div>
        <x-tab selected="Tab 1">
            <x-tab.items tab="Manage Holiday">
                <div class="grid grid-cols-2 md:grid-cols-2 gap-4 mb-3">
                    <x-input label="Holiday" wire:model.defer="newHoliday" clearable />
                    <x-date label="Date" wire:model.defer="newDate" format="YYYY-MM-DD" />
                </div>

                <x-button text="Add" icon="plus" color="emerald" wire:click="addHoliday" class="mb-4"/>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            @foreach ($headers as $header)
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ $header['label'] }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($rows as $index => $row)
                            <tr>
                                <td class="px-6 py-4">{{ $row['holiday'] }}</td>
                                <td class="px-6 py-4">{{ $row['date'] }}</td>
                                <td class="px-6 py-4">
                                    <button 
                                        wire:click="remove({{ $index }})"
                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-sm">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            </x-tab.items>
            <x-tab.items tab="Upload DTR">
                <div class="space-y-4 p-4">
                    <x-upload label="Upload DTR" wire:model="dtrFile" />
                    <x-button text="Upload" color="emerald" wire:click="uploadDTR" />
                </div>
            </x-tab.items>
            <x-tab.items tab="Manage Signatory">
            <div class="grid grid-cols-2 gap-2 mb-4 p-4">
                <x-input label="Name" wire:model.defer="signatoryName" />
                <x-input label="Designation" wire:model.defer="signatoryPosition" />
            </div>
                <div class="col-span-2 flex justify-center p-4">
            <x-button 
                text="Add" 
                color="emerald" 
                wire:click="AddSignatory" 
                icon="plus"
                class="px-4 py-2"
            />
        </div>
            <div class="col-span-2 w-full overflow-x-auto">
                <table class="min-w-full w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Designation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($signatories as $index => $signatory)
                            <tr>
                                <td class="px-6 py-4">{{ $signatory['Name'] }}</td>
                                <td class="px-6 py-4">{{ $signatory['Designation'] }}</td>
                                <td class="px-6 py-4">
                                    <button 
                                        wire:click="removeSignatory({{ $index }})"
                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-sm">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-tab.items>

        </x-tab>

</div>
