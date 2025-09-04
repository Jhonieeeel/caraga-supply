<div>
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Report of Supplies and Materials Issued
    </h2>
    <div class="bg-teal-100 p-3 rounded my-5 sm:my-3">
        <p class="text-sm text-teal-800">
            <span class="font-semibold text-teal-600 pr-1" aria-hidden="true">Note:</span>
            Supplies listed below are from <span class="font-semibold">completed</span>
            requisitions only.
        </p>
    </div>

    <form wire:submit.prevent="createRsmi" class="sm:pb-4 my-4 sm:pt-6 grid grid-cols-1 sm:grid-cols-3 sm:gap-4 gap-3">
        <div class="sm:col-span-2 col-span-1">
            <x-date range helpers wire:model.live.1000ms="rsmiDate" label="Date" hint="Select your Date of Report"
                format="DD [of] MMMM [of] YYYY" />
        </div>
        <div class="sm:col-span-2 col-span-1">
            <x-button text="Submit" submit />
        </div>
    </form>
    <div class="-m-1.5 overflow-x-auto">
        <div class="p-1.5 min-w-full inline-block align-middle">
            <div class="overflow-hidden">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight sm:py-4">
                    Supplies Report — Showing All Transactions This Month
                </h2>
                <div class="border">
                    <x-table :$headers :rows="$this->getSupplies" paginate>
                        @interact('column_action', $item)
                            <x-button.circle wire:click="createRsmi({{ $item['stock_id'] }})" text="RSMI" color="teal"
                                loading="" icon="document" />
                        @endinteract
                    </x-table>
                </div>
            </div>
        </div>
    </div>
</div>

