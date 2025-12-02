<div>
    <h2 class="font-semibold text-xl text-gray-800 leading-tight capitalize">
        Report on the Physical count of Inventories
    </h2>
    <div class="bg-teal-100 p-3 rounded my-5 sm:my-3">
        <p class="text-sm text-teal-800">
            <span class="font-semibold text-teal-600 pr-1" aria-hidden="true">Note:</span>
            The supplies listed below are from stocks that have been <span class="font-semibold">touched</span> or
            transacted with.
        </p>
    </div>
    <form wire:submit.prevent="submit" class="sm:pb-4 my-4 sm:pt-6 grid grid-cols-1 sm:grid-cols-3 sm:gap-4 gap-3">
        <div class="sm:col-span-2 col-span-1">
            <x-date range helpers wire:model="transactionDate" label="Date"
                hint="Select your Date of Transaction Report" format="DD [of] MMMM [of] YYYY" />
        </div>
        <div class="sm:col-span-2 col-span-1">
            <x-select.styled label="Select Category" hint="You can choose 1, 2, 3 or 4" :options="[
                ['label' => 'Non-Food Items', 'value' => 'nfi'],
                ['label' => 'Supplies', 'value' => 'supplies'],
                ['label' => 'Fuel', 'value' => 'fuel'],
                ['label' => 'Others', 'value' => 'others'],
            ]" searchable />
        </div>
        <div class="sm:col-span-2 col-span-1">
            <x-button text="Submit" submit />
        </div>
    </form>
    <h2 class="font-semibold text-xl text-gray-800 leading-tight py-3 sm:py-4">
        Supplies Report — Showing All Transactions This Month
    </h2>
    <x-table :headers="$headers" :rows="$this->rows ?? []" filter :quantity="[2, 5, 10]">
        @interact('column_action', $transaction)
            <x-button.circle wire:click="createRpci({{ $transaction->stock_id }})" text="{{ $transaction->id }}"
                color="teal" loading icon="document-chart-bar" />
        @endinteract
    </x-table>
</div>

