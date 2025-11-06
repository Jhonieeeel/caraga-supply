<div class="space-y-6">
    <div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8 bg-white border shadow rounded">
        <div class="flex items-center justify-between border-b border-gray-200 pb-3">
            <h2 class="text-lg font-semibold text-gray-800 tracking-tight flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c1.657 0 3-1.343 3-3S13.657 2 12 2 9 3.343 9 5s1.343 3 3 3zM5.121 17.804A8.966 8.966 0 0112 15c1.657 0 3.18.45 4.508 1.234M12 22a10 10 0 10-7.071-2.929A9.993 9.993 0 0012 22z" />
                </svg>
                Procurement Project Description
            </h2>
        </div>

        <div class="details space-y-6 sm:py-6 p-3.5">
            @if ($procurement)
                <div
                    class="bg-white shadow-sm hover:shadow-md transition-shadow duration-300 rounded-lg p-6 border border-gray-100">
                    <h5 class="text-sm uppercase text-primary-600 mb-4 tracking-wider font-semibold">
                        Annual Procurement Plan
                    </h5>

                    <div class="divide-y divide-gray-100">
                        <div class="py-3 flex items-start text-sm">
                            <span class="w-48 text-gray-500 font-medium">Code PAP:</span>
                            <span class="text-gray-800 font-semibold">{{ $procurement->code }}</span>
                        </div>

                        <div class="py-3 flex items-start text-sm">
                            <span class="w-48 text-gray-500 font-medium">Project Title:</span>
                            <span class="text-gray-800 font-semibold">{{ $procurement->project_title }}</span>
                        </div>

                        <div class="py-3 flex items-start text-sm">
                            <span class="w-48 text-gray-500 font-medium">Estimated Budget Total:</span>
                            <span class="text-gray-800 font-semibold">₱
                                {{ $procurement->estimated_budget_total }}</span>
                        </div>

                        <div class="py-3 flex items-start text-sm">
                            <span class="w-48 text-gray-500 font-medium">Mode of Procurement:</span>
                            <span class="text-gray-800 font-semibold">{{ $procurement->mode_of_procurement }}</span>
                        </div>
                    </div>
                </div>
            @endif
            <hr>
            @if ($procurement->purchaseRequest)
                <div
                    class="purchase-request bg-white border border-gray-200 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
                    <!-- Header -->
                    <div class="flex flex-wrap items-center justify-between mb-4">
                        <h5 class="text-sm uppercase text-primary-600 font-semibold tracking-wider">Purchase Request
                        </h5>

                        <div class="flex flex-wrap gap-2 mt-2 sm:mt-0">
                            <x-button icon="printer" color="teal" wire:click="redirectRequest" position="left" flat>
                                Print
                            </x-button>
                            <x-button icon="pencil" wire:click="editRequest({{ $procurement->purchaseRequest }})"
                                color="cyan" position="left" flat>
                                Edit
                            </x-button>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="divide-y divide-gray-100">
                        @php
                            $pr = $procurement->purchaseRequest;
                        @endphp

                        <div class="py-3 flex flex-col sm:flex-row text-sm">
                            <span class="w-56 text-gray-500 font-medium">Purchase Request Number:</span>
                            <span class="text-gray-800 font-semibold">{{ $pr?->pr_number ?? 'N/A' }}</span>
                        </div>

                        <div class="py-3 flex flex-col sm:flex-row text-sm">
                            <span class="w-56 text-gray-500 font-medium">Input Date:</span>
                            <span
                                class="text-gray-800 font-semibold">{{ $pr?->input_date?->format('Y-m-d') ?? 'N/A' }}</span>
                        </div>

                        <div class="py-3 flex flex-col sm:flex-row text-sm">
                            <span class="w-56 text-gray-500 font-medium">Date Posted:</span>
                            <span
                                class="text-gray-800 font-semibold">{{ $pr?->date_posted?->format('Y-m-d') ?? 'N/A' }}</span>
                        </div>

                        <div class="py-3 flex flex-col sm:flex-row text-sm">
                            <span class="w-56 text-gray-500 font-medium">Closing Date:</span>
                            <span
                                class="text-gray-800 font-semibold">{{ $pr?->closing_date?->format('Y-m-d') ?? 'N/A' }}</span>
                        </div>

                        <div class="py-3 flex flex-col sm:flex-row text-sm">
                            <span class="w-56 text-gray-500 font-medium">APP/SPP (PDF):</span>
                            @if ($pr?->app_spp_pdf_file)
                                <a href="{{ asset('storage/' . $pr->app_spp_pdf_file) }}"
                                    class="text-blue-600 hover:text-blue-800 underline font-semibold" target="_blank">
                                    {{ $pr?->app_spp_pdf_filename ?? 'NO_NAME' }}.pdf
                                </a>
                            @else
                                <span class="text-gray-800 font-semibold">N/A</span>
                            @endif
                        </div>

                        <div class="py-3 flex flex-col sm:flex-row text-sm">
                            <span class="w-56 text-gray-500 font-medium">PhilGEPS (PDF):</span>
                            @if ($pr?->philgeps_pdf_file)
                                <a href="{{ asset('storage/' . $pr->philgeps_pdf_file) }}"
                                    class="text-blue-600 hover:text-blue-800 underline font-semibold" target="_blank">
                                    {{ $pr?->philgeps_pdf_filename ?? 'NO_NAME' }}.pdf
                                </a>
                            @else
                                <span class="text-gray-800 font-semibold">N/A</span>
                            @endif
                        </div>

                        <div class="py-3 flex flex-col sm:flex-row text-sm">
                            <span class="w-56 text-gray-500 font-medium">ABC Based on (APP):</span>
                            <span class="text-gray-800 font-semibold">₱
                                {{ $pr?->AbcBasedApp->estimated_budget_total ?? 'N/A' }}
                            </span>
                        </div>

                        <div class="py-3 flex flex-col sm:flex-row text-sm">
                            <span class="w-56 text-gray-500 font-medium">ABC:</span>
                            <span class="text-gray-800 font-semibold">₱ {{ $pr?->abc ?? 'N/A' }}</span>
                        </div>

                        <div class="py-3 flex flex-col sm:flex-row text-sm">
                            <span class="w-56 text-gray-500 font-medium">Email Posting:</span>
                            <span class="text-gray-800 font-semibold">{{ $pr?->email_posting ?? 'N/A' }}</span>
                        </div>

                        <div class="py-3 flex flex-col sm:flex-row text-sm">
                            <span class="w-56 text-gray-500 font-medium">APP Year:</span>
                            <span class="text-gray-800 font-semibold">{{ $pr?->AppYear->app_year ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            @endif
            @if ($procurement->purchaseOrder)
                <div
                    class="purchase-order bg-white border border-gray-200 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
                    <div class="flex flex-wrap items-center justify-between mb-4">
                        <h5 class="text-sm uppercase text-primary-600 font-semibold tracking-wider">Purchase Order</h5>

                        <div class="flex flex-wrap gap-2 mt-2 sm:mt-0">
                            <x-button icon="printer" color="teal" disabled position="left" wire:click="printOrder"
                                flat>
                                Print
                            </x-button>
                            <x-button icon="pencil" color="cyan" position="left"
                                wire:click="editOrder({{ $procurement->purchaseOrder }})" flat>
                                Edit
                            </x-button>
                        </div>
                    </div>

                    <div class="sm:flex sm:gap-x-8">
                        <div class="flex-1 divide-y divide-gray-100">
                            @php $po = $procurement->purchaseOrder; @endphp

                            <div class="py-3 flex flex-col sm:flex-row text-sm">
                                <span class="w-56 text-gray-500 font-medium">Purchase Order Number:</span>
                                <span class="text-gray-800 font-semibold">{{ $po?->po_number ?? 'N/A' }}</span>
                            </div>

                            <div class="py-3 flex flex-col sm:flex-row text-sm">
                                <span class="w-56 text-gray-500 font-medium">Purchase Order Date:</span>
                                <span
                                    class="text-gray-800 font-semibold">{{ $po?->po_date?->format('Y-m-d') ?? 'N/A' }}</span>
                            </div>

                            <div class="py-3 flex flex-col sm:flex-row text-sm">
                                <span class="w-56 text-gray-500 font-medium">NOA:</span>
                                <span
                                    class="text-gray-800 font-semibold">{{ $po?->noa?->format('Y-m-d') ?? 'N/A' }}</span>
                            </div>

                            <div class="py-3 flex flex-col sm:flex-row text-sm">
                                <span class="w-56 text-gray-500 font-medium">NTP:</span>
                                <span
                                    class="text-gray-800 font-semibold">{{ $po?->ntp?->format('Y-m-d') ?? 'N/A' }}</span>
                            </div>

                            <div class="py-3 flex flex-col sm:flex-row text-sm">
                                <span class="w-56 text-gray-500 font-medium">Resolution Number:</span>
                                <span
                                    class="text-gray-800 font-semibold">{{ $po?->resolution_number ?? 'N/A' }}</span>
                            </div>

                            <div class="py-3 flex flex-col sm:flex-row text-sm">
                                <span class="w-56 text-gray-500 font-medium">Delivery Date:</span>
                                <span
                                    class="text-gray-800 font-semibold">{{ $po?->delivery_date?->format('Y-m-d') ?? 'N/A' }}</span>
                            </div>

                            <div class="py-3 flex flex-col sm:flex-row text-sm">
                                <span class="w-56 text-gray-500 font-medium">Supplier:</span>
                                <span class="text-gray-800 font-semibold">{{ $po?->supplier ?? 'N/A' }}</span>
                            </div>

                            <div class="py-3 flex flex-col sm:flex-row text-sm">
                                <span class="w-56 text-gray-500 font-medium">Contract Price:</span>
                                <span class="text-gray-800 font-semibold">{{ $po?->contract_price ?? 'N/A' }}</span>
                            </div>

                            <div class="py-3 flex flex-col sm:flex-row text-sm">
                                <span class="w-56 text-gray-500 font-medium">Variance:</span>
                                <span class="text-gray-800 font-semibold">{{ $po?->variance ?? 'N/A' }}</span>
                            </div>

                            <div class="py-3 flex flex-col sm:flex-row text-sm">
                                <span class="w-56 text-gray-500 font-medium">Email Link:</span>
                                <span class="text-gray-800 font-semibold">{{ $po?->email_link ?? 'N/A' }}</span>
                            </div>

                            <div class="py-3 flex flex-col sm:flex-row text-sm">
                                <span class="w-56 text-gray-500 font-medium">Date Posted (PR):</span>
                                <span
                                    class="text-gray-800 font-semibold">{{ $po?->datePosted?->date_posted?->format('Y-m-d') ?? 'N/A' }}</span>
                            </div>

                            <div class="py-3 flex flex-col sm:flex-row text-sm">
                                <span class="w-56 text-gray-500 font-medium">ABC Based (APP):</span>
                                <span class="text-gray-800 font-semibold">₱
                                    {{ $po?->abcBasedApp->estimated_budget_total ?? 'N/A' }}</span>
                            </div>

                            <div class="py-3 flex flex-col sm:flex-row text-sm">
                                <span class="w-56 text-gray-500 font-medium">ABC Based (PR):</span>
                                <span class="text-gray-800 font-semibold">₱
                                    {{ $po?->purchaseRequest->abc ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="flex-1 divide-y divide-gray-100 mt-6 sm:mt-0">
                            <div class="py-3 flex flex-col sm:flex-row text-sm">
                                <span class="w-56 text-gray-500 font-medium">NTP (PDF):</span>
                                @if ($po?->ntp_pdf_file)
                                    <a href="{{ asset('storage/' . $po->ntp_pdf_file) }}"
                                        class="text-blue-600 hover:text-blue-800 underline font-semibold"
                                        target="_blank">NTP FILE.pdf</a>
                                @else
                                    <span class="text-gray-800 font-semibold">N/A</span>
                                @endif
                            </div>

                            <div class="py-3 flex flex-col sm:flex-row text-sm">
                                <span class="w-56 text-gray-500 font-medium">NOA (PDF):</span>
                                @if ($po?->noa_pdf_file)
                                    <a href="{{ asset('storage/' . $po->noa_pdf_file) }}"
                                        class="text-blue-600 hover:text-blue-800 underline font-semibold"
                                        target="_blank">NOA FILE.pdf</a>
                                @else
                                    <span class="text-gray-800 font-semibold">N/A</span>
                                @endif
                            </div>

                            <div class="py-3 flex flex-col sm:flex-row text-sm">
                                <span class="w-56 text-gray-500 font-medium">PO (PDF):</span>
                                @if ($po?->po_pdf_file)
                                    <a href="{{ asset('storage/' . $po->po_pdf_file) }}"
                                        class="text-blue-600 hover:text-blue-800 underline font-semibold"
                                        target="_blank">PO FILE.pdf</a>
                                @else
                                    <span class="text-gray-800 font-semibold">N/A</span>
                                @endif
                            </div>

                            <div class="py-3 flex flex-col sm:flex-row text-sm">
                                <span class="w-56 text-gray-500 font-medium">RESO (PDF):</span>
                                @if ($po?->reso_pdf_file)
                                    <a href="{{ asset('storage/' . $po->reso_pdf_file) }}"
                                        class="text-blue-600 hover:text-blue-800 underline font-semibold"
                                        target="_blank">RESO FILE.pdf</a>
                                @else
                                    <span class="text-gray-800 font-semibold">N/A</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <x-modal title="Purchase Requests" id="update-request" size="4xl">
        <div class="sm:py-4 py-2">
            <p class="text-sm text-gray-500">Purchase Requests</p>
        </div>

        <form wire:submit.prevent="submitEditRequest" class=" w-full space-y-6" enctype="multipart/form-data">
            <div class="grid grid-cols-2 gap-4">
                <x-date label="Closing Date *" wire:model="requestForm.closing_date" format="YYYY-MM-DD" />
                <x-input label="Purchase Request Number *" wire:model="requestForm.pr_number" />
                <x-input label="ABC *" wire:model="requestForm.abc" />
                <x-date label="Date Posted *" wire:model="requestForm.date_posted" format="YYYY-MM-DD" />
                <x-date label="Input Date *" wire:model="requestForm.input_date" format="YYYY-MM-DD" />
                <x-input label="Email Posting *" wire:model="requestForm.email_posting" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-upload accept="application/pdf" wire:model="updateAppFile" label="APP/SPP (PDF) *"
                    hint="Please upload APP/SPP document." tip="Upload our Signed RIS here" />
                <x-input label="APP/SPP (PDF) (Filename) *" wire:model="requestForm.app_spp_pdf_filename" />
                <x-upload accept="application/pdf" wire:model="updatePhilFile" label="Philgeps Posting (PDF) *"
                    hint="Please upload PhilGeps document." tip="Upload our Signed RIS here" />
                <x-input label="Philgeps Posting (Filename) *" wire:model="requestForm.philgeps_pdf_filename" />

            </div>
            {{-- file --}}
            <div class="w-full flex justify-end">
                <x-button submit icon="cube" position="right">Submit</x-button>
            </div>
        </form>
    </x-modal>

    <x-modal title="Purchase Order" id="update-order" size="4xl">
        <div class="sm:py-4 py-2">
            <p class="text-sm text-gray-500">Purchase Order</p>
        </div>

        <form wire:submit.prevent="submitEditOrder" class="w-full space-y-4" enctype="multipart/form-data">
            <div class="grid grid-cols-2 gap-4">
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
            </div>

            <div class="sm:py-4 py-2">
                <p class="text-sm text-gray-500">Purchase Order Files</p>
            </div>

            {{-- files --}}
            <div class="grid grid-cols-2 gap-4">
                <x-upload accept="application/pdf" wire:model="updateNtpFile" label="NTP Document (PDF) *" />
                <x-upload accept="application/pdf" wire:model="updateNoaFile" label="NOA Document (PDF) *" />
                <x-upload accept="application/pdf" wire:model="updatePoFile" label="PO Document (PDF) *" />
                <x-upload accept="application/pdf" wire:model="updateResoFile" label="RESOLUTION Document (PDF) *" />
            </div>
            <div class="flex justify-end">
                <x-button submit icon="cube" position="right">Submit</x-button>
            </div>
        </form>
    </x-modal>
</div>

