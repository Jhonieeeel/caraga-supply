<div>
    @if ($requisition)
        @php
            $isApproved =
                $requisition->requested_by &&
                $requisition->approved_by &&
                $requisition->issued_by &&
                $requisition->received_by;

            $pdf = $requisition->pdf;
        @endphp
        <div>
            <x-step wire:model.live="step" panels>
                <x-step.items step="1" title="Admin Approval" description="Your request has been processed">
                    @if (!$requisition->completed)
                        <small
                            class="text-sm py-6 text-center">{{ $isApproved
                                ? "Generate the RIS and proceed to 'Next' "
                                : 'Please wait while the requisition is being approved by all parties.' }}</small>
                        <div class="sm:py-3">
                            @if ($isApproved)
                                <x-button wire:click="getRIS" loading="getRIS" text="Generate RIS" icon="document"
                                    position="right" color="teal" outline />
                            @endif
                        </div>
                    @else
                        <small class="text-sm py-6 text-center">Proceed to Next to view PDF</small>
                    @endif
                    <div class="flex justify-end w-full">
                        <x-button wire:click="$set('step', 2)" :disabled="!$requisition?->pdf">
                            Next
                        </x-button>
                    </div>
                </x-step.items>
                <x-step.items step="2" title="RIS Ready to Print" description="Proceed to print the RIS.">
                    <small class="text-sm py-6 text-center">You can now print the generated
                        RIS.</small>
                    <div class="sm:py-3 py-2 flex sm:justify-center">
                        <iframe src="{{ asset('storage/' . $requisition->pdf) }}" width="70%" height="500px"
                            frameborder="0" class="border border-gray-300 mt-4">
                        </iframe>
                    </div>
                    <div class="sm:pt-4 pt-3 flex justify-between w-full">
                        <x-button wire:click="$set('step', 1)">
                            Previous
                        </x-button>
                        <x-button wire:click="$set('step', 3)" :disabled="!$requisition->pdf">
                            Next
                        </x-button>
                    </div>
                </x-step.items>
                <x-step.items step="3" title="Upload Signed Document" description="Upload the signed RIS.">
                    <small class="text-sm py-6 text-center">Update RIS. </small>
                    @if (!$requisition->completed)
                        <form wire:submit.prevent="updateRIS" enctype="multipart/form-data">
                            <div class="sm:py-4 py-2">
                                <x-upload accept="application/pdf" wire:model="temporaryFile" label="RIS Document"
                                    hint="Please upload RIS document." tip="Upload our Signed RIS here" />
                            </div>
                            <div class="sm:pt-4 pt-3 flex justify-between w-full">
                                <x-button wire:click="$set('step', 2)">
                                    Previous
                                </x-button>
                                <x-button submit :disabled="!$this->temporaryFile">
                                    Finish
                                </x-button>
                            </div>
                        </form>
                    @else
                        <div class="sm:pt-4 pt-3 flex justify-between w-full">
                            <x-button wire:click="$set('step', 2)">
                                Previous
                            </x-button>
                            <x-button wire:click="$set('step', 4)">
                                Finish
                            </x-button>
                        </div>
                    @endif
                </x-step.items>
            </x-step>
        </div>
    @else
        <small class="text-sm text-gray-500">No Selected Requisition Yet.</small>
        <button class="text-sm block cursor-pointer underline" wire:click="$set('tab', 'Requests List')">Go
            back</button>
    @endif
</div>

