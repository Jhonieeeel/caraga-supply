<div class="space-y-6">
    <div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8 bg-white border shadow rounded">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-gray-200 pb-3">
            <h2 class="flex items-center gap-3 text-lg font-semibold text-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c1.657 0 3-1.343 3-3S13.657 2 12 2 9 3.343 9 5s1.343 3 3 3zM5.121 17.804A8.966 8.966 0 0112 15c1.657 0 3.18.45 4.508 1.234M12 22a10 10 0 10-7.071-2.929A9.993 9.993 0 0012 22z" />
                </svg>
                <span>Purchase Request</span>
                <span class="ml-2 bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full">
                    {{ $request->pr_number }}
                </span>
            </h2>
        </div>

        <div class="details space-y-6 sm:py-6 p-3.5">

            {{-- Type Selector --}}
            <div class="grid grid-cols-2 gap-4 sm:mb-12">
                <x-select.styled wire:model.live="type" label="Type of Purchase Request *" :options="['Meals', 'Services', 'Transportation', 'Workbook', 'Admin & Janitorial']" />
            </div>

            {{-- Section Router --}}
            @if ($type === 'Meals')
                @include('livewire.pages.afms.components.partials.pr.meals')
            @elseif ($type === 'Workbook')
                @include('livewire.pages.afms.components.partials.pr.workbook')
            @elseif ($type === 'Transportation')
                @include('livewire.pages.afms.components.partials.pr.transportation')
            @elseif ($type === 'Services')
                @include('livewire.pages.afms.components.partials.pr.service')
            @elseif ($type === 'Admin & Janitorial')
                @include('livewire.pages.afms.components.partials.pr.admin-janitorial')
            @endif

        </div>
    </div>
</div>

