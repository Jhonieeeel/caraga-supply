<div class="space-y-6">
    <div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8 bg-white border shadow rounded">
        {{-- Flash Messages --}}
        @if (session()->has('success'))
            <div class="mb-4 px-4 py-3 rounded bg-green-100 text-green-800 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mb-4 px-4 py-3 rounded bg-red-100 text-red-800 text-sm">
                {{ session('error') }}
            </div>
        @endif
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-gray-200 pb-3">
            <h2 class="flex items-center gap-3 text-lg font-semibold text-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Purchase Order</span>
                <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded-full">
                    {{ $request->pr_number }}
                </span>
                @if ($request->purchaseOrder?->po_number)
                    <span class="ml-1 bg-indigo-100 text-indigo-800 text-xs font-semibold px-2 py-1 rounded-full">
                        PO# {{ $request->purchaseOrder->po_number }}
                    </span>
                @endif
            </h2>
        </div>
        <div class="details space-y-6 sm:py-6 p-3.5">
            {{-- Type Selector --}}
            <div class="grid grid-cols-2 gap-4 sm:mb-12">
                <x-select.styled wire:model.live="type" label="Type of Purchase Order *"
                    :options="['Meals', 'Services', 'Transportation', 'Workbook', 'Admin & Janitorial']" />
            </div>
            {{-- Section Router --}}
            @if ($type === 'Meals')
                @include('livewire.pages.afms.components.partials.po.meals')
            @else
                <div class="text-sm text-gray-500 italic">
                    PO template for <strong>{{ $type }}</strong> is coming soon.
                </div>
            @endif
        </div>
    </div>
</div>