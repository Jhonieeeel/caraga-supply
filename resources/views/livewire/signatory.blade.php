<div>
    <div class="grid grid-cols-2 gap-2 mb-4 p-4">
        <x-input label="Name" wire:model.defer="signatoryName" />
        <x-input label="Designation" wire:model.defer="signatoryPosition" />
    </div>
    <div class="col-span-2 flex justify-center p-4">
        <x-button text="Add" color="emerald" wire:click="AddSignatory" icon="plus" class="px-4 py-2" />
    </div>
    <div class="col-span-2 w-full overflow-x-auto">
        <table class="min-w-full w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Name
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Designation</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($signatories as $index => $signatory)
                    <tr>
                        <td class="px-6 py-4">{{ $signatory['Name'] }}</td>
                        <td class="px-6 py-4">{{ $signatory['Designation'] }}</td>
                        <td class="px-6 py-4">
                            <button wire:click="removeSignatory({{ $index }})"
                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-sm">
                                Remove
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

