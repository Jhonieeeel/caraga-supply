<div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8 bg-white border shadow rounded">
    <div class="flex items-center justify-between sm:pb-4">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            User Management
        </h2>
        @role('Super Admin')
            <x-button x-on:click="$modalOpen('add-user')" icon="users" position="right">Add User</x-button>
        @endrole
    </div>
    {{-- content --}}
    <x-table :$headers :rows="$this->rows" filter :quantity="[2, 5, 10]" paginate loading>
        @interact('column_role', $row)
            @foreach ($row->roles as $role)
                <x-badge text="{{ $role->name }}" color="blue" />
            @endforeach
        @endinteract
    </x-table>

    {{-- add user modal --}}
    <x-modal title="Add User Form" id="add-user">
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
                <x-select.styled wire:model='supplyForm.unit' label="Unit of Measure *" hint="Select unit of measure"
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
                <x-button submit icon="plus-circle" position="right">Submit</x-button>
            </div>
        </form>
    </x-modal>
</div>

