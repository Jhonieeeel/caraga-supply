<div>
    <div class="grid grid-cols-2 md:grid-cols-2 gap-4 mb-3">
        <x-input label="Holiday" wire:model.defer="newHoliday" clearable />
        <x-date label="Date" wire:model.defer="newDate" format="YYYY-MM-DD" />
    </div>

    <x-button text="Add" icon="plus" color="emerald" wire:click="addHoliday" class="mb-4" />

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
                        <button wire:click="remove({{ $index }})"
                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-sm">
                            Remove
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

