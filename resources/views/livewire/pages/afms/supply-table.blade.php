<div>
    <div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8 bg-white border shadow rounded">
        <div class="flex items-center justify-between sm:pb-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Supply
            </h2>
            @role('Super Admin')
                <x-button md x-on:click="$modalOpen('add')" icon="cube" position="right">Add Supply</x-button>
            @endrole
        </div>
        <div class="overflow-hidden sm:rounded-lg">
            <div class="p-6 text-gray-900">
                @if (session('message'))
                    <div class="sm:py-4">
                        <x-alert title="{{ session('message')['title'] }}" text="{{ session('message')['text'] }}"
                            color="{{ session('message')['color'] }}" light />
                    </div>
                @endif
                <x-table :$headers :rows='$this->rows' :filter="['quantity' => 'quantity', 'search' => 'search']" :quantity="[2, 5, 10]" paginate loading>
                    @role('Super Admin')
                        @interact('column_action', $supply)
                            <x-button.circle color="red" icon="trash" wire:click="delete('{{ $supply->id }}')" />
                            <x-button.circle color="teal" icon="pencil-square" wire:click='edit({{ $supply }})' />
                        @endinteract
                    @endrole
                </x-table>
            </div>
        </div>

        {{-- modal --}}
        <x-modal title="Add Supply Form" id="add">
            <form wire:submit.prevent='create'>
                <div class="grid sm:grid-cols-2 sm:gap-3 gap-5">
                    <x-input wire:model='supplyForm.name' label="Name *" hint="Supply name" />
                    <x-select.styled wire:model='supplyForm.category' label="Category *" hint="Select category"
                        :options="[
                            ['label' => 'Non-Food Items', 'value' => 'nfi'],
                            ['label' => 'Supplies', 'value' => 'supplies'],
                            ['label' => 'Fuel', 'value' => 'fuel'],
                            ['label' => 'Others', 'value' => 'others'],
                        ]" searchable />
                    <x-select.styled wire:model='supplyForm.unit' label="Unit of Measure *"
                        hint="Select unit of measure" :options="[
                            ['label' => 'Pc', 'value' => 'pc'],
                            ['label' => 'Pack', 'value' => 'pack'],
                            ['label' => 'Sachet', 'value' => 'sachet'],
                            ['label' => 'Unit', 'value' => 'unit'],
                            ['label' => 'Ream', 'value' => 'ream'],
                            ['label' => 'Box', 'value' => 'box'],
                            ['label' => 'Set', 'value' => 'set'],
                            ['label' => 'Meter', 'value' => 'meter'],
                            ['label' => 'Kg', 'value' => 'kg'],
                            ['label' => 'Bag', 'value' => 'bag'],
                            ['label' => 'Case', 'value' => 'case'],
                            ['label' => 'Kit', 'value' => 'kit'],
                            ['label' => 'Lot', 'value' => 'lot'],
                            ['label' => 'Bucket', 'value' => 'bucket'],
                            ['label' => 'Galon', 'value' => 'galon'],
                            ['label' => 'Crate', 'value' => 'crate'],
                            ['label' => 'Bottle', 'value' => 'bottle'],
                        ]" lazy="10" searchable />
                </div>
                <div class="sm:pt-2">
                    <x-button md submit icon="plus-circle" position="right">Submit</x-button>
                </div>
            </form>
        </x-modal>

        {{-- edit --}}
        <x-modal title="Edit Supply Form" id="edit">
            <form wire:submit.prevent='update'>
                <div class="grid sm:grid-cols-2 sm:gap-3 gap-5">
                    <x-input wire:model='supplyForm.name' label="Name *" hint="Supply name" />
                    <x-select.styled wire:model='supplyForm.category' label="Category *" hint="Select category"
                        :options="[
                            ['label' => 'Non-Food Items', 'value' => 'nfi'],
                            ['label' => 'Supplies', 'value' => 'supplies'],
                            ['label' => 'Fuel', 'value' => 'fuel'],
                            ['label' => 'Others', 'value' => 'others'],
                        ]" searchable />
                    <x-select.styled wire:model='supplyForm.unit' label="Unit *" hint="Select unit of measure"
                        :options="[
                            ['label' => 'Pc', 'value' => 'pc'],
                            ['label' => 'Pack', 'value' => 'pack'],
                            ['label' => 'Sachet', 'value' => 'sachet'],
                            ['label' => 'Unit', 'value' => 'unit'],
                            ['label' => 'Ream', 'value' => 'ream'],
                            ['label' => 'Box', 'value' => 'box'],
                            ['label' => 'Set', 'value' => 'set'],
                            ['label' => 'Meter', 'value' => 'meter'],
                            ['label' => 'Kg', 'value' => 'kg'],
                            ['label' => 'Bag', 'value' => 'bag'],
                            ['label' => 'Case', 'value' => 'case'],
                            ['label' => 'Kit', 'value' => 'kit'],
                            ['label' => 'Lot', 'value' => 'lot'],
                            ['label' => 'Bucket', 'value' => 'bucket'],
                            ['label' => 'Galon', 'value' => 'galon'],
                            ['label' => 'Crate', 'value' => 'crate'],
                            ['label' => 'Bottle', 'value' => 'bottle'],
                        ]" lazy="10" searchable />
                </div>
                <div class="sm:pt-2">
                    <x-button md submit icon="plus-circle" position="right">Submit</x-button>
                </div>
            </form>
        </x-modal>
    </div>
</div>

