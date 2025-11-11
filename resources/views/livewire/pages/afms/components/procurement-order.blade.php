<div>
    <div class="flex justify-between items-center">
        <h4 class="text-sm">Purchase Orders</h4>
        <x-button x-on:click="$modalOpen('add-order')" icon="cube" position="right">Add
            Entry</x-button>
    </div>

    <x-table :$headers :rows='$this->rows' loading filter :quantity="[2, 5, 10]">
        @interact('column_ntp', $order)
            <span class="text-nowrap">
                {{ $order->ntp?->format('Y-m-d') }}
            </span>
        @endinteract
        @interact('column_noa', $order)
            <span class="text-nowrap">
                {{ $order->noa?->format('Y-m-d') }}
            </span>
        @endinteract
        @interact('column_resolution_number', $order)
            <span class="text-nowrap">
                {{ $order->resolution_number }}
            </span>
        @endinteract
        @interact('column_action', $order)
            <div class="sm:flex items-center gap-2">
                <x-button.circle color="teal" icon="magnifying-glass"
                    wire:click="viewDetails({{ $order->procurement }})" />
                <x-button.circle color="teal" icon="receipt-percent" />
            </div>
        @endinteract
    </x-table>

    <x-modal title="Purchase Order" id="add-order" size="4xl">
        <div class="sm:py-4 py-2">
            <p class="text-sm text-gray-500">Purchase Order</p>
        </div>

        <form wire:submit.prevent="onSubmit" class="grid grid-cols-2 gap-4 w-full" enctype="multipart/form-data">
            <x-select.styled wire:model.live='orderForm.purchase_request_id' label="PR Number *" :options="$this->getPr"
                searchable />
            <x-input label="PO Number *" wire:model="orderForm.po_number" />
            <x-date label="PO Date *" wire:model="orderForm.po_date" format="YYYY-MM-DD" />
            <x-date label="NOA *" wire:model="orderForm.noa" format="YYYY-MM-DD" />
            <x-date label="Ntp *" wire:model="orderForm.ntp" format="YYYY-MM-DD" />
            <x-input label="Resolution Number *" wire:model="orderForm.resolution_number" />
            <x-date label="Delivery Date *" wire:model="orderForm.delivery_date" format="YYYY-MM-DD" />
            <x-input label="Supplier *" wire:model="orderForm.supplier" />
            <x-number label="Contact Price *" wire:model.live="orderForm.contract_price" />
            <x-number label="Variance *" :value="$this->variance" readonly />
            <x-input label="Email Link *" wire:model="orderForm.email_link" />
            <div class="col-span-1">
                <x-upload accept="application/pdf" wire:model="po_pdf_file" label="PO Document (PDF) *" />
            </div>
            <div class="col-span-2 ml-auto">
                <x-button submit icon="cube" position="right">Submit</x-button>
            </div>
        </form>
    </x-modal>
</div>

