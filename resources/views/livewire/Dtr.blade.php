<div class="w-full mt-10 p-2">
    <div class="p-2 max-w-7xl mx-auto bg-slate-200 shadow-md rounded-xl border-all">
        <div class="grid grid-cols-2 gap-4 p-2">
            <x-select.styled label="Name:" :options="[
            ['label' => 'TALL', 'value' => 1],
            ['label' => 'LIVT', 'value' => 2],
            ]" searchable icon="name"/>
            <x-input label="DTR Number:" icon="cog" />
        </div>
        <form wire:submit.prevent="submitDate">
            <div class="grid grid-cols-2 gap-4 p-2">
                <x-select.styled label="Year"
                    wire:model="year"
                    placeholder="Select an Option"
                    :options="[2024,2025]" />
                <x-select.styled label="Month"
                    wire:model="month"
                    placeholder="Select an Option"
                    :options="[
                    ['label' => 'January', 'value' => 1],
                    ['label' => 'February', 'value' => 2],
                    ['label' => 'March', 'value' => 3],
                    ['label' => 'April', 'value' => 4],
                    ['label' => 'May', 'value' => 5],
                    ['label' => 'June', 'value' => 6],
                    ['label' => 'July', 'value' => 7],
                    ['label' => 'August', 'value' => 8],
                    ['label' => 'September', 'value' => 9],
                    ['label' => 'October', 'value' => 10],
                    ['label' => 'November', 'value' => 11],
                    ['label' => 'Decemeber', 'value' => 12],
                    ]" />
            </div> 
            <div class="flex justify-center item-center gap-2 p-2">
                <x-button submit round text="Display" color="emerald" icon="document-check"/>
                <x-button round icon="pencil" color="slate" x-on:click="$modalOpen('modal-rectify')">Rectify</x-button>
                <x-button round icon="pencil-square" color="slate">Signatory</x-button>
                <x-button round icon="printer" color="slate">CSC Form No.48</x-button>
            </div>
        </form>
    </div>
    <div class="p-2 max-w-7xl mx-auto bg-slate-200 shadow-md rounded-xl border-all mt-10">
        <div class="flex flex-col">
    <div class="-m-1.5 overflow-x-auto">
        <div class="p-1.5 min-w-full inline-block align-middle">
        <div class="border border-gray-200 rounded-lg overflow-hidden dark:border-neutral-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead>
                <tr>
                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">Day</th>
                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">Weekday</th>
                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">AM Arrival</th>
                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">AM Departure</th>
                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">PM Arrival</th>
                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">PM Departure</th>
                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">Scan Logs</th>
                
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                    @foreach ($thisMonth as $etc)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-neutral-200">
                                {{ $etc['day'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                                {{ $etc['weekday'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        </div>
    </div>
    </div>
        <x-modal id='modal-rectify' justify-center size="5xl">
            <x-slot name="title">
                <h1 class="text-center w-full">Month of</h1>
            </x-slot>
            <form>
                <p label="Name" id="MdName"></p>
            </form>
        </x-modal>
    </div>
</div>
