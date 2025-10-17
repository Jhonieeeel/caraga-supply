<div>
    <div class="flex justify-between items-center">
        <h4 class="text-sm">Purchase Orders</h4>
        <x-button x-on:click="$modalOpen('add-entry')" icon="cube" position="right">Add
            Entry</x-button>
    </div>

    <x-modal title="Annual Procurment Plan" id="add-request" size="3xl">
        <p class="text-sm text-gray-500">Annual Procurement Plan.</p>

    </x-modal>
</div>

