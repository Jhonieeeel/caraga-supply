<div class="space-y-6">
    <div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8 bg-white border shadow rounded">
        <div class="flex items-center justify-between sm:pb-4">
            <h2 class="font-semibold sm:text-lg text-sm text-gray-800 leading-tight">
                Procurement Project Description
            </h2>

            {{-- x-on:click="$modalOpen('add-request')" --}}
        </div>

        <div class="details space-y-6 sm:py-6 p-3.5">
            <div class="annual-plan hover:shadow p-4">
                <h5 class="text-sm uppercase text-primary-600 mb-3 font-semibold">Annual Procurement Plan</h5>
                <div class="overflow-hidden sm:rounded-lg space-y-1.5 p-4">
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
            <div class="purchase-request hover:shadow p-4">
                <div class="flex items-center justify-between">
                    <h5 class="text-sm uppercase text-primary-600 mb-3 font-semibold">Purchase Request</h5>
                    <div class="sm:flex gap-x-3">
                        <x-button icon="printer" color="teal" position="left" wire:click="printRequest"
                            flat>Print</x-button>
                        <x-button icon="pencil" color="cyan" position="left"
                            wire:click="editRequest({{ $procurement->purchaseRequest }})" flat>Edit</x-button>
                    </div>
                </div>
                <div class="overflow-hidden sm:rounded-lg space-y-1.5 p-4">
                    <p class="text-gray-500 font-medium text-sm">
                        Closing Date:
                        <span class="text-gray-600 font-semibold px-3">
                            {{ $procurement->purchaseRequest?->closing_date ?? 'N/A' }}
                        </span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">Input Date: <span
                            class="text-gray-600 font-semibold px-3">
                            {{ $procurement->purchaseRequest?->input_date ?? 'N/A' }} </span></p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">APP/SPP (PDF):
                        @if ($procurement->purchaseRequest?->app_spp_pdf_file)
                            <a href="{{ asset('storage/pr-records/' . $procurement->purchaseRequest->app_spp_pdf_file) }}"
                                class="text-blue-600 underline font-semibold px-3" target="_blank">
                                {{ $procurement->purchaseRequest?->app_spp_pdf_filename }}.pdf
                            </a>
                        @else
                            <span class="text-gray-600 font-semibold px-3">
                                'N/A'
                            </span>
                        @endif
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">PhilGeps (PDF): <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseRequest?->philgeps_pdf_file ?? 'N/A' }}</span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">PR Number: <span
                            class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseRequest?->pr_number ?? 'N/A' }}</span>
                    </p>

                    <p class="text-gray-500 font-medium text-sm space-x-6">ABC Based on (APP): <span
                            class="text-gray-600 font-semibold px-3">
                            {{ $procurement->purchaseRequest->AbcBasedApp->estimated_budget_total ?? 'N/A' }}</span>
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
                            {{ $procurement->purchaseRequest?->date_posted ?? 'N/A' }}
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

            @if ($procurement->purchaseOrder)
                <hr>
                <div class="purchase-order hover:shadow p-4">
                    <div class="flex items-center justify-between">
                        <h5 class="text-sm uppercase text-primary-600 mb-3 font-semibold">Purchase Order</h5>
                        <div class="sm:flex gap-x-3">
                            <x-button icon="printer" color="teal" position="left" wire:click="printOrder"
                                flat>Print</x-button>
                            <x-button icon="pencil" color="cyan" position="left"
                                wire:click="editOrder({{ $procurement->purchaseOrder }})" flat>Edit</x-button>
                        </div>
                    </div>
                    <div class="overflow-hidden sm:rounded-lg space-y-1.5 p-4">

                        <p class="text-gray-500 font-medium text-sm space-x-6">NOA: <span
                                class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseOrder?->no ?? 'N/A' }}
                            </span>
                        </p>

                        <p class="text-gray-500 font-medium text-sm space-x-6">Variance: <span
                                class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseOrder?->variance ?? 'N/A' }}</span>
                        </p>

                        <p class="text-gray-500 font-medium text-sm space-x-6">Variance: <span
                                class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseOrder?->po_number ?? 'N/A' }}</span>
                        </p>

                        <p class="text-gray-500 font-medium text-sm space-x-6">Date Posted ( PR ): <span
                                class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseRequest?->date_poste ?? 'N/A' }}</span>
                        </p>

                        <p class="text-gray-500 font-medium text-sm space-x-6">Purchase Order Date: <span
                                class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseOrder?->po_dat ?? 'N/A' }}</span>
                        </p>

                        <p class="text-gray-500 font-medium text-sm space-x-6">Delivery Date: <span
                                class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseOrder?->delivery_date->format('Y-m-d') ?? 'N/A' }}</span>
                        </p>

                        <p class="text-gray-500 font-medium text-sm space-x-6">NTP: <span
                                class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseOrder?->ntp->format('Y-m-d') ?? 'N/A' }}</span>
                        </p>

                        <p class="text-gray-500 font-medium text-sm space-x-6">Resolution Number: <span
                                class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseOrder?->resolution_number ?? 'N/A' }}</span>
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
                                class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseOrder?->abcBasedApp->estimated_budget_total ?? 'N/A' }}</span>
                        </p>

                        <p class="text-gray-500 font-medium text-sm space-x-6">ABC Based (PR): <span
                                class="text-gray-600 font-semibold px-3">{{ $procurement->purchaseRequest->purchaseOrder?->abc->abc ?? 'N/A' }}</span>
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- edit request --}}
    <x-modal title="Purchase Requests" id="update-request" size="4xl">
        <div class="sm:py-4 py-2">
            <p class="text-sm text-gray-500">Purchase Requests</p>
        </div>

        <form wire:submit.prevent="onSubmit" class="grid grid-cols-2 gap-4 w-full" enctype="multipart/form-data">
            <x-date label="Closing Date *" wire:model="requestForm.closing_date" format="YYYY-MM-DD" />
            <x-input label="Purchase Request Number *" wire:model="requestForm.pr_number" />
            <x-input label="ABC *" wire:model="requestForm.abc" />
            <x-date label="Date Posted *" wire:model="requestForm.date_posted" format="YYYY-MM-DD" />
            <x-date label="Input Date *" wire:model="requestForm.input_date" format="YYYY-MM-DD" />
            <x-upload accept="application/pdf" wire:model="requestForm.app_spp_pdf_file" label="APP/SPP (PDF) *"
                hint="Please upload APP/SPP document." tip="Upload our Signed RIS here" />
            <x-input label="APP/SPP (PDF) (Filename) *" wire:model="requestForm.app_spp_pdf_filename" />
            <x-upload accept="application/pdf" wire:model="requestForm.philgeps_pdf_file"
                label="Philgeps Posting (PDF) *" hint="Please upload PhilGeps document."
                tip="Upload our Signed RIS here" />
            <x-input label="Philgeps Posting (Filename) *" wire:model="requestForm.philgeps_pdf_filename" />
            <x-input label="Email Posting *" wire:model="requestForm.email_posting" />

            <div class="col-span-2">
            </div>
            <div class="col-span-2 ml-auto">
                <x-button submit icon="cube" position="right">Submit</x-button>
            </div>
        </form>
    </x-modal>

    {{-- edit order --}}
    <x-modal title="Purchase Order" id="update-order" size="4xl">
        <div class="sm:py-4 py-2">
            <p class="text-sm text-gray-500">Purchase Order</p>
        </div>

        <form wire:submit.prevent="" class="grid grid-cols-2 gap-4 w-full" enctype="multipart/form-data">
            <x-date label="PO Date *" wire:model="orderForm.po_date" format="YYYY-MM-DD" />
            <x-date label="NOA *" wire:model="orderForm.noa" format="YYYY-MM-DD" />
            <x-number label="Variance *" wire:model="orderForm.variance" />
            <x-input label="PO Number *" wire:model="orderForm.po_number" />
            <x-date label="Delivery Date *" wire:model="orderForm.delivery_date" format="YYYY-MM-DD" />
            <x-date label="Ntp *" wire:model="orderForm.ntp" format="YYYY-MM-DD" />
            <x-input label="Resolution Number *" wire:model="orderForm.resolution_number" />
            <x-input label="Supplier *" wire:model="orderForm.supplier" />
            <x-number label="Contact Price *" wire:model="orderForm.contact_price" />
            <x-input label="Email Link *" wire:model="orderForm.email_link" />
            <div class="col-span-1">
                <x-upload accept="application/pdf" wire:model="orderForm.po_document" label="PO Document (PDF) *" />
            </div>
            <div class="col-span-2 ml-auto">
                <x-button submit icon="cube" position="right">Submit</x-button>
            </div>
        </form>
    </x-modal>
</div>

