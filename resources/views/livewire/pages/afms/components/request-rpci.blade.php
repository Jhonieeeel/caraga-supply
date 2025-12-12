<div>
    <h2 class="font-semibold text-xl text-gray-800 leading-tight capitalize">
        Report on the Physical count of Inventories ( Stock Card)
    </h2>
    <div class="bg-teal-100 p-3 rounded my-5 sm:my-3">
        <p class="text-sm text-teal-800">
            <span class="font-semibold text-teal-600 pr-1" aria-hidden="true">Note:</span>
            The supplies listed below are from stocks that have been <span class="font-semibold">touched</span> or
            transacted with.
        </p>

    </div>
    <form wire:submit.prevent="submitDate" class="sm:pb-4 my-4 sm:pt-6 grid grid-cols-1 sm:grid-cols-3 sm:gap-4 gap-3">
        <div class="sm:col-span-2 col-span-1">
            <x-date range helpers wire:model="transactionDate" label="Date" hint="Select your Date of Report"
                format="DD [of] MMMM [of] YYYY" />
        </div>
        <div class="sm:col-span-2 col-span-1">
            <x-button text="Submit" submit />
        </div>
    </form>
    <h2 class="font-semibold text-xl text-gray-800 leading-tight py-3 sm:py-4">
        Supplies Report — Showing All Transactions This Month
    </h2>
</div>

