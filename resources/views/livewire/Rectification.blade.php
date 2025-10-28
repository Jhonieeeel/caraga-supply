<div class="p-2 max-w-7xl mx-auto bg-slate-200 shadow-md rounded-xl border-all">
    <x-tab selected="Rectification">
        <x-tab.items tab="Rectification">
            <div class="grid grid-cols-2 gap-4 p-2">
                <x-select.styled label="Name:" :options="[['label' => 'TALL', 'value' => 1], ['label' => 'LIVT', 'value' => 2]]" searchable icon="name" />
                <x-input label="DTR Number:" icon="magnifying-glass" />
            </div>
            <form wire:submit.prevent="submitDate">
                <div class="grid grid-cols-2 gap-4 p-2">
                    <x-select.styled label="Year" wire:model="year" placeholder="Select an Option"
                        :options="[2024, 2025]" />
                    <x-select.styled label="Month" wire:model="month" placeholder="Select an Option"
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
                <div x-data="{ showActions: false, openSignatoryMenu: false }" class="flex justify-center items-center gap-2 p-2">
                    <x-button submit round text="Display" color="emerald" icon="document-check"
                        @click="showActions = !showActions; openSignatoryMenu = false" />
                    <template x-if="showActions">
                        <div class="flex items-center gap-2">
                            <x-button round icon="pencil" color="slate" x-on:click="$modalOpen('modal-rectify')">
                                Rectify
                            </x-button>
                            <div class="relative inline-block">
                                <x-button round icon="pencil-square" color="slate"
                                    @click="openSignatoryMenu = !openSignatoryMenu">
                                    Signatory
                                </x-button>
                                <div x-show="openSignatoryMenu" @click.away="openSignatoryMenu = false" x-transition
                                    class="absolute right-0 mt-2 w-48 bg-white border rounded shadow-lg z-50">
                                    <button wire:click="option1"
                                        class="block px-2 py-2 w-full text-left hover:bg-gray-100">
                                        Blank
                                    </button>
                                    <button wire:click="option2"
                                        class="block px-2 py-2 w-full text-left hover:bg-gray-100">
                                        Lorene Sia-Cathedral
                                    </button>
                                    <button wire:click="option3"
                                        class="block px-2 py-2 w-full text-left hover:bg-gray-100">
                                        Marie Lynn B. Tadle
                                    </button>
                                </div>
                            </div>
                            <div x-data="{ openCSCMenu: false }" class="relative inline-block">
                                <x-button round icon="printer" color="slate" @click="openCSCMenu = !openCSCMenu">
                                    CSC Form No.48
                                </x-button>

                                <div x-show="openCSCMenu" @click.away="openCSCMenu = false" x-transition
                                    class="absolute right-0 mt-2 w-48 bg-white border rounded shadow-lg z-50">
                                    <button wire:click="option1"
                                        class="block px-2 py-2 w-full text-left hover:bg-gray-100">
                                        All
                                    </button>
                                    <button wire:click="option2"
                                        class="block px-2 py-2 w-full text-left hover:bg-gray-100">
                                        1–15
                                    </button>
                                    <button wire:click="option3"
                                        class="block px-2 py-2 w-full text-left hover:bg-gray-100">
                                        16–31
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
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
                                <th scope="col"
                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                    Day</th>
                                <th scope="col"
                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                    Weekday</th>
                                <th scope="col"
                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                    AM Arrival</th>
                                <th scope="col"
                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                    AM Departure</th>
                                <th scope="col"
                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                    PM Arrival</th>
                                <th scope="col"
                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                    PM Departure</th>
                                <th scope="col"
                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                    Scan Logs</th>

                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                            @foreach ($thisMonth as $etc)
                                <tr>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-neutral-200">
                                        {{ $etc['day'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $etc['weekday'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                                    </td>
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
            <label for="MdName" class="font-semibold">Name:</label>
            <p id="MdName" class="inline-block"></p>
            <x-select.native label="Select Day:" :options="[
                1,
                2,
                3,
                4,
                5,
                6,
                7,
                8,
                9,
                10,
                11,
                12,
                13,
                14,
                15,
                16,
                17,
                18,
                19,
                20,
                21,
                22,
                23,
                24,
                25,
                26,
                27,
                28,
                29,
                30,
                31,
            ]" />
            <div x-data="{
                rows: [{ hour: '', minutes: '', action: '' }],
                addRow() {
                    this.rows.push({ hour: '', minutes: '', action: '' })
                },
                removeRow(index) {
                    this.rows.splice(index, 1)
                },
                resetRows() {
                    this.rows = [{ hour: '', minutes: '', action: '' }]
                }
            }" class="space-y-4">
                <template x-for="(row, index) in rows" :key="index">
                    <div class="flex flex-col sm:flex-row items-end gap-4 mt-3">
                        <x-number min="0" max="24" label="Hour:" class="w-full sm:w-24"
                            x-model="row.hour" />
                        <x-number min="0" max="59" label="Minutes:" class="w-full sm:w-24"
                            x-model="row.minutes" />
                        <x-select.native label="Action:" :options="['In', 'Out', 'OB', 'OT', 'Duty Staff In', 'Duty Staff Out', 'On Leave', 'Day Off']" class="w-full sm:w-40"
                            x-model="row.action" />

                        <x-button outline icon="minus" red x-show="rows.length > 1" x-on:click="removeRow(index)"
                            color="red">
                        </x-button>
                    </div>
                </template>

                <div class="flex gap-3 mt-3">
                    <x-button outline icon="plus" x-on:click="addRow()">
                    </x-button>

                    <x-button outline icon="arrow-path-rounded-square" x-on:click="resetRows()">
                    </x-button>
                </div>
            </div>
            <br>
            <br>
            <br>
            <br>
            <br>
            <x-button text="Save" color="green" icon="archive-box-arrow-down" />
            <div class="mt-5 bg-white">
                <div class="overflow-hidden rounded-lg border border-gray-300">
                    <table class="min-w-full border-collapse">
                        <thead class="bg-gray-300">
                            <tr>
                                @foreach ($headers as $header)
                                    <th class="border-b border-gray-300 px-4 py-2 text-left">
                                        {{ $header['label'] }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $index => $row)
                                <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-100' }} hover:bg-gray-200">
                                    <td class="border-b border-gray-200 px-4 py-2">{{ $row->created_at }}</td>
                                    <td class="border-b border-gray-200 px-4 py-2">{{ $row->in_out }}</td>
                                    <td class="border-b border-gray-200 px-4 py-2">
                                        <button wire:click="deleteUser({{ $row->id }})"
                                            onclick="return confirm('Are you sure?')"
                                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </form>
    </x-modal>
    </x-tab.items>
    <x-tab.items tab="Holiday">
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
    </x-tab.items>
    <x-tab.items tab="DTR">
        <div class="space-y-4 p-4">
            <x-upload label="Upload DTR" wire:model="dtrFile" />
            <x-button text="Upload" color="emerald" wire:click="uploadDTR" />
        </div>
    </x-tab.items>
    <x-tab.items tab="Signature">
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
    </x-tab.items>
    </x-tab>
</div>

