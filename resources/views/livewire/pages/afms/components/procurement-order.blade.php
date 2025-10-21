<div>
    <div class="flex justify-between items-center">
        <h4 class="text-sm">Purchase Orders</h4>
        <x-button x-on:click="$modalOpen('add-entry')" icon="cube" position="right">Add
            Entry</x-button>
    </div>

    <x-table :$headers :rows='$this->rows' filter :quantity="[2, 5, 10]">
        @interact('column_action', $order)
            <div class="sm:flex items-center gap-2">
                <x-button.circle color="teal" icon="magnifying-glass" />
                <x-button.circle color="teal" icon="receipt-percent" />
            </div>
        @endinteract
    </x-table>

    <x-modal title="Annual Procurment Plan" id="add-request" size="3xl">
        <p class="text-sm text-gray-500">Annual Procurement Plan.</p>
    </x-modal>
</div>

