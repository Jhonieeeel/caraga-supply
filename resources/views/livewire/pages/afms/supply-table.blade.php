<div class="sm:py-6">
        <div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8">
            <div class="flex items-center justify-between sm:pb-4">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Supply') }}
                </h2>
                <x-modal title="Add Supply Form" id="add">
                    <div class="grid sm:grid-cols-2 sm:gap-3 gap-5">
                        <x-input label="Name *" hint="Supply name" />
                        <x-select.styled label="Category *" hint="Select category" :options="[
                            ['label' => 'Non-Food Items', 'value' => 'nfi'],
                            ['label' => 'Supplies', 'value' => 'supplies'],
                            ['label' => 'Fuel', 'value' => 'fuel'],
                            ['label' => 'Others', 'value' => 'others'],
                        ]" searchable />
                        <x-select.styled label="Unit of Measure *" hint="Select unit of measure" :options="[
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
]

                    " lazy="10" searchable />
                    </div>
                    <div class="sm:pt-2">
                        <x-button submit icon="plus-circle" position="right">Submit</x-button>
                    </div>
                </x-modal>
                <x-button x-on:click="$modalOpen('add')" icon="cube" position="right">Add Supply</x-button>
            </div>
            <div class="overflow-hidden sm:rounded-lg">
                <div class="p-6 text-gray-900">
                      <x-table :$headers :$rows :filter="['quantity' => 'quantity', 'search' => 'search']" :quantity="[2,5,10]" loading paginate>
                        @interact('column_action', $row) 
                            <x-button.circle color="red"
                                            icon="trash"
                                            wire:click="delete('{{ $row->id }}')" />
                        @endinteract
                      </x-table>
                </div>
            </div>
        </div>
</div>
