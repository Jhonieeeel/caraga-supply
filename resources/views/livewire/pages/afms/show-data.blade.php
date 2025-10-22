<div class="space-y-6">
    <div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8 bg-white border shadow rounded">
        <div class="flex items-center justify-between sm:pb-4">
            <h2 class="font-semibold sm:text-lg text-sm text-gray-800 leading-tight">
                Procurement Project Description
            </h2>

            {{-- x-on:click="$modalOpen('add-request')" --}}
            <x-button wire:click="procurement" icon="pencil" position="left">Edit Request</x-button>
        </div>

        <div class="details space-y-6 sm:py-6 p-3.5">
            <div class="annual-plan">
                <h5 class="text-sm uppercase text-primary-600 mb-3 font-semibold">Annual Procurement Plan</h5>
                <div class="overflow-hidden sm:rounded-lg space-y-1.5 ">
                    <p class="text-gray-500 font-medium text-sm space-x-6">Code PAP: <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->code }}</span></p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">Project Title: <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->project_title }}</span></p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">Estimated Budget Total: <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->estimated_budget_total }}</span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">Mode of Procurement: <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->mode_of_procurement }}</span></p>
                </div>
            </div>

            <hr>
            <div class="purchase-request">
                <div class="flex items-center justify-between">
                    <h5 class="text-sm uppercase text-primary-600 mb-3 font-semibold">Purchase Request</h5>
                    <x-button icon="printer" color="teal" position="left" wire:click="printRequest">Print</x-button>
                </div>
                <div class="overflow-hidden sm:rounded-lg space-y-1.5 ">
                    <p class="text-gray-500 font-medium text-sm">
                        Closing Date:
                        <span class="text-gray-600 font-semibold px-3">
                            {{ $procurement->purchaseRequest->closing_date?->format('F j, Y') ?? 'N/A' }}
                        </span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">Input Date: <span
                            class="text-gray-600 font-semibold px-3">
                            {{ $procurement->purchaseRequest->input_date?->format('F j, Y') ?? 'N/A' }} </span></p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">APP/SPP (PDF): <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseRequest->app_spp_pdf_file ?? 'N/A' }}</span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">PhilGeps (PDF): <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseRequest->philgeps_pdf_file ?? 'N/A' }}</span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">PR Number: <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseRequest->pr_number ?? 'N/A' }}</span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">ABC Based on (APP): <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->estimated_budget_total ?? 'N/A' }}</span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">ABC: <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseRequest->abc ?? 'N/A' }}</span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">Email Posting: <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseRequest->email_posting ?? 'N/A' }}</span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm">
                        Date Posted:
                        <span class="text-gray-600 font-semibold px-3">
                            {{ $procurement->purchaseRequest->date_posted?->format('F j, Y') ?? 'N/A' }}
                        </span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm">
                        APP Year:
                        <span class="text-gray-600 font-semibold px-3">
                            {{ $procurement?->app_year ?? 'N/A' }}
                        </span>
                    </p>

                </div>
            </div>

            <hr>
            <div class="purchase-order">
                <div class="flex items-center justify-between">
                    <h5 class="text-sm uppercase text-primary-600 mb-3 font-semibold">Purchase Order</h5>
                    <x-button icon="printer" color="teal" position="left" wire:click="printOrder">Print</x-button>
                </div>
                <div class="overflow-hidden sm:rounded-lg space-y-1.5">
                    <p class="text-gray-500 font-medium text-sm space-x-6">Order Number: <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseOrder?->order_number ?? 'N/A' }}</span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">NOA: <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseOrder?->noa?->format('F j, Y') ?? 'N/A' }}
                        </span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">Variance: <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseOrder?->variance ?? 'N/A' }}</span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">Variance: <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseOrder?->po_number ?? 'N/A' }}</span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">Date Posted ( PR ): <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseRequest->date_posted?->format('F j, Y') ?? 'N/A' }}</span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">Purchase Order Date: <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseOrder?->po_date?->format('F j, Y') ?? 'N/A' }}</span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">Delivery Date: <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseOrder?->delivery_date?->format('F j, Y') ?? 'N/A' }}</span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">NTP: <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseOrder?->ntp?->format('F j, Y') ?? 'N/A' }}</span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">Resolution Number: <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseOrder?->resolution_number?->format('F j, Y') ?? 'N/A' }}</span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">Supplier: <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseOrder?->supplier ?? 'N/A' }}</span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">Contact Price: <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseOrder?->contact_price ?? 'N/A' }}</span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">Email Link: <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseOrder?->email_link ?? 'N/A' }}</span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">ABC Based (APP): <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseOrder?->abc_based_app ?? 'N/A' }}</span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">ABC Based (PR): <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseRequest->abc ?? 'N/A' }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

