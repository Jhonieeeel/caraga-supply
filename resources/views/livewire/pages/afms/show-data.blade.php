<div class="space-y-6">
    <div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8 bg-white border shadow rounded">
        <div class="flex items-center justify-between sm:pb-4">
            <h2 class="font-semibold sm:text-lg text-sm text-gray-800 leading-tight">
                Procurement Project Description
            </h2>

            {{-- x-on:click="$modalOpen('add-request')" --}}
        </div>

        <div class="details space-y-6 sm:py-6 p-3.5">

            @if ($procurement)
                <div class="annual-plan hover:shadow p-4">
                    <h5 class="text-sm uppercase text-primary-600 mb-3 font-semibold">Annual Procurement Plan</h5>
                    <div class="overflow-hidden sm:rounded-lg space-y-2 p-4">

                        <div class="flex text-sm">
                            <span class="w-56 text-gray-500 font-medium">Code PAP:</span>
                            <span class="text-gray-600 font-semibold">{{ $procurement->code }}</span>
                        </div>

                        <div class="flex text-sm">
                            <span class="w-56 text-gray-500 font-medium">Project Title:</span>
                            <span class="text-gray-600 font-semibold">{{ $procurement->project_title }}</span>
                        </div>

                        <div class="flex text-sm">
                            <span class="w-56 text-gray-500 font-medium">Estimated Budget Total:</span>
                            <span class="text-gray-600 font-semibold">{{ $procurement->estimated_budget_total }}</span>
                        </div>

                        <div class="flex text-sm">
                            <span class="w-56 text-gray-500 font-medium">Mode of Procurement:</span>
                            <span class="text-gray-600 font-semibold">{{ $procurement->mode_of_procurement }}</span>
                        </div>

                    </div>
                </div>
            @endif
            <hr>
            @if ($procurement->purchaseRequest)
                <div class="purchase-request hover:shadow p-4">
                    <div class="flex items-center justify-between">
                        <h5 class="text-sm uppercase text-primary-600 mb-3 font-semibold">Purchase Request</h5>
                        <div class="sm:flex gap-x-3">
                            <x-button icon="printer" color="teal" position="left" wire:click="printRequest"
                                flat>Print</x-button>
                            <x-button icon="pencil" wire:click="editRequest({{ $procurement->purchaseRequest }})"
                                color="cyan" position="left" flat>Edit</x-button>
                        </div>
                    </div>

                    <div class="overflow-hidden sm:rounded-lg space-y-2 p-4">

                        <div class="flex text-sm">
                            <span class="w-56 text-gray-500 font-medium">Purchase Request Number:</span>
                            <span
                                class="text-gray-600 font-semibold">{{ $procurement->purchaseRequest?->pr_number ?? 'N/A' }}</span>
                        </div>

                        <div class="flex text-sm">
                            <span class="w-56 text-gray-500 font-medium">Input Date:</span>
                            <span
                                class="text-gray-600 font-semibold">{{ $procurement->purchaseRequest?->input_date?->format('Y-m-d') ?? 'N/A' }}</span>
                        </div>

                        <div class="flex text-sm">
                            <span class="w-56 text-gray-500 font-medium">Date Posted:</span>
                            <span
                                class="text-gray-600 font-semibold">{{ $procurement->purchaseRequest?->date_posted?->format('Y-m-d') ?? 'N/A' }}</span>
                        </div>

                        <div class="flex text-sm">
                            <span class="w-56 text-gray-500 font-medium">Closing Date:</span>
                            <span
                                class="text-gray-600 font-semibold">{{ $procurement->purchaseRequest?->closing_date?->format('Y-m-d') ?? 'N/A' }}</span>
                        </div>

                        <div class="flex text-sm">
                            <span class="w-56 text-gray-500 font-medium">APP/SPP (PDF):</span>
                            @if ($procurement->purchaseRequest?->app_spp_pdf_file)
                                <a href="{{ asset('storage/' . $procurement->purchaseRequest->app_spp_pdf_file) }}"
                                    class="text-blue-600 underline font-semibold" target="_blank">
                                    {{ $procurement->purchaseRequest?->app_spp_pdf_filename }}.pdf
                                </a>
                            @else
                                <span class="text-gray-600 font-semibold">N/A</span>
                            @endif
                        </div>

                        <div class="flex text-sm">
                            <span class="w-56 text-gray-500 font-medium">PhilGEPS (PDF):</span>
                            @if ($procurement->purchaseRequest?->philgeps_pdf_file)
                                <a href="{{ asset('storage/' . $procurement->purchaseRequest->philgeps_pdf_file) }}"
                                    class="text-blue-600 underline font-semibold" target="_blank">
                                    {{ $procurement->purchaseRequest?->philgeps_pdf_filename }}.pdf
                                </a>
                            @else
                                <span class="text-gray-600 font-semibold">N/A</span>
                            @endif
                        </div>

                        <div class="flex text-sm">
                            <span class="w-56 text-gray-500 font-medium">ABC Based on (APP):</span>
                            <span
                                class="text-gray-600 font-semibold">{{ $procurement->purchaseRequest->AbcBasedApp->estimated_budget_total ?? 'N/A' }}</span>
                        </div>

                        <div class="flex text-sm">
                            <span class="w-56 text-gray-500 font-medium">ABC:</span>
                            <span
                                class="text-gray-600 font-semibold">{{ $procurement->purchaseRequest->abc ?? 'N/A' }}</span>
                        </div>

                        <div class="flex text-sm">
                            <span class="w-56 text-gray-500 font-medium">Email Posting:</span>
                            <span
                                class="text-gray-600 font-semibold">{{ $procurement->purchaseRequest->email_posting ?? 'N/A' }}</span>
                        </div>

                        <div class="flex text-sm">
                            <span class="w-56 text-gray-500 font-medium">APP Year:</span>
                            <span
                                class="text-gray-600 font-semibold">{{ $procurement->purchaseRequest?->AppYear->app_year ?? 'N/A' }}</span>
                        </div>

                    </div>
                </div>
            @endif
            @if ($procurement->purchaseOrder)
                <div class="purchase-order hover:shadow p-4">
                    {{-- buttons --}}
                    <div class="flex items-center justify-between">
                        <h5 class="text-sm uppercase text-primary-600 mb-3 font-semibold">Purchase Order</h5>
                        <div class="sm:flex gap-x-3">
                            <x-button icon="printer" color="teal" position="left" wire:click="printOrder"
                                flat>Print</x-button>
                            <x-button icon="pencil" color="cyan" position="left"
                                wire:click="editOrder({{ $procurement->purchaseOrder }})" flat>Edit</x-button>
                        </div>
                    </div>
                    <div class="sm:flex gap-x-6 items-start">
                        <div class="overflow-hidden sm:rounded-lg space-y-2 p-4">
                            {{-- po numberr --}}
                            <div class="flex text-sm">
                                <span class="w-56 text-gray-500 font-medium">Purchase Order Number:</span>
                                <span
                                    class="text-gray-600 font-semibold">{{ $procurement->purchaseOrder?->po_number ?? 'N/A' }}</span>
                            </div>
                            {{-- po date --}}
                            <div class="flex text-sm">
                                <span class="w-56 text-gray-500 font-medium">Purchase Order Date:</span>
                                <span
                                    class="text-gray-600 font-semibold">{{ $procurement->purchaseOrder?->po_date?->format('Y-m-d') ?? 'N/A' }}</span>
                            </div>
                            {{-- noa --}}
                            <div class="flex text-sm">
                                <span class="w-56 text-gray-500 font-medium">NOA:</span>
                                <span
                                    class="text-gray-600 font-semibold">{{ $procurement->purchaseOrder?->noa?->format('Y-m-d') ?? 'N/A' }}</span>
                            </div>
                            {{-- ntp --}}
                            <div class="flex text-sm">
                                <span class="w-56 text-gray-500 font-medium">NTP:</span>
                                <span
                                    class="text-gray-600 font-semibold">{{ $procurement->purchaseOrder?->ntp?->format('Y-m-d') ?? 'N/A' }}</span>
                            </div>
                            {{-- reso number --}}
                            <div class="flex text-sm">
                                <span class="w-56 text-gray-500 font-medium">Resolution Number:</span>
                                <span
                                    class="text-gray-600 font-semibold">{{ $procurement->purchaseOrder?->resolution_number ?? 'N/A' }}</span>
                            </div>
                            {{-- delivery date --}}
                            <div class="flex text-sm">
                                <span class="w-56 text-gray-500 font-medium">Delivery Date:</span>
                                <span
                                    class="text-gray-600 font-semibold">{{ $procurement->purchaseOrder?->delivery_date?->format('Y-m-d') ?? 'N/A' }}</span>
                            </div>
                            {{-- supplier --}}
                            <div class="flex text-sm">
                                <span class="w-56 text-gray-500 font-medium">Supplier:</span>
                                <span
                                    class="text-gray-600 font-semibold">{{ $procurement->purchaseOrder?->supplier ?? 'N/A' }}</span>
                            </div>
                            {{-- contract price --}}
                            <div class="flex text-sm">
                                <span class="w-56 text-gray-500 font-medium">Contract Price:</span>
                                <span
                                    class="text-gray-600 font-semibold">{{ $procurement->purchaseOrder?->contract_price ?? 'N/A' }}</span>
                            </div>
                            {{-- variance --}}
                            <div class="flex text-sm">
                                <span class="w-56 text-gray-500 font-medium">Variance:</span>
                                <span
                                    class="text-gray-600 font-semibold">{{ $procurement->purchaseOrder?->variance ?? 'N/A' }}</span>
                            </div>
                            {{-- email --}}
                            <div class="flex text-sm">
                                <span class="w-56 text-gray-500 font-medium">Email Link:</span>
                                <span
                                    class="text-gray-600 font-semibold">{{ $procurement->purchaseOrder?->email_link ?? 'N/A' }}</span>
                            </div>
                            <div class="flex text-sm">
                                <span class="w-56 text-gray-500 font-medium">Date Posted (PR):</span>
                                <span
                                    class="text-gray-600 font-semibold">{{ $procurement->purchaseOrder?->datePosted?->date_posted?->format('Y-m-d') ?? 'N/A' }}</span>
                            </div>

                            <div class="flex text-sm">
                                <span class="w-56 text-gray-500 font-medium">ABC Based (APP):</span>
                                <span
                                    class="text-gray-600 font-semibold">{{ $procurement->purchaseOrder?->abcBasedApp->estimated_budget_total ?? 'N/A' }}</span>
                            </div>

                            <div class="flex text-sm">
                                <span class="w-56 text-gray-500 font-medium">ABC Based (PR):</span>
                                <span
                                    class="text-gray-600 font-semibold">{{ $procurement->purchaseOrder?->purchaseRequest->abc ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="overflow-hidden sm:rounded-lg space-y-2 p-4">
                            <div class="flex text-sm">
                                <span class="w-56 text-gray-500 font-medium">NTP (PDF):</span>
                                @if ($procurement->purchaseOrder?->ntp_pdf_file)
                                    <a href="{{ asset('storage/' . $procurement->purchaseOrder?->ntp_pdf_file) }}"
                                        class="text-blue-600 underline font-semibold" target="_blank">
                                        NTP FILE.pdf
                                    </a>
                                @else
                                    <span class="text-gray-600 font-semibold">N/A</span>
                                @endif
                            </div>
                            <div class="flex text-sm">
                                <span class="w-56 text-gray-500 font-medium">NOA (PDF):</span>
                                @if ($procurement->purchaseOrder?->noa_pdf_file)
                                    <a href="{{ asset('storage/' . $procurement->purchaseOrder?->noa_pdf_file) }}"
                                        class="text-blue-600 underline font-semibold" target="_blank">
                                        NOA FILE.pdf
                                    </a>
                                @else
                                    <span class="text-gray-600 font-semibold">N/A</span>
                                @endif
                            </div>
                            <div class="flex text-sm">
                                <span class="w-56 text-gray-500 font-medium">PO (PDF):</span>
                                @if ($procurement->purchaseOrder?->po_pdf_file)
                                    <a href="{{ asset('storage/' . $procurement->purchaseOrder?->po_pdf_file) }}"
                                        class="text-blue-600 underline font-semibold" target="_blank">
                                        PO FILE.pdf
                                    </a>
                                @else
                                    <span class="text-gray-600 font-semibold">N/A</span>
                                @endif
                            </div>
                            <div class="flex text-sm">
                                <span class="w-56 text-gray-500 font-medium">RESO (PDF):</span>
                                @if ($procurement->purchaseOrder?->reso_pdf_file)
                                    <a href="{{ asset('storage/' . $procurement->purchaseOrder?->reso_pdf_file) }}"
                                        class="text-blue-600 underline font-semibold" target="_blank">
                                        PO FILE.pdf
                                    </a>
                                @else
                                    <span class="text-gray-600 font-semibold">N/A</span>
                                @endif
                            </div>
                        </div>
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

    {{-- edit order --}}
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

