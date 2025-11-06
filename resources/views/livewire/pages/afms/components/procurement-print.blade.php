<div class="space-y-6">
    <div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8 bg-white border shadow rounded">
        <div class="flex items-center justify-between border-b border-gray-200 pb-3">
            <h2 class="flex items-center gap-3 text-lg font-semibold text-gray-800">
                <!-- Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c1.657 0 3-1.343 3-3S13.657 2 12 2 9 3.343 9 5s1.343 3 3 3zM5.121 17.804A8.966 8.966 0 0112 15c1.657 0 3.18.45 4.508 1.234M12 22a10 10 0 10-7.071-2.929A9.993 9.993 0 0012 22z" />
                </svg>

                <!-- Title -->
                <span>Purchase Request</span>

                <!-- PR Number Badge -->
                <span class="ml-2 bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full">
                    {{ $request->pr_number }}
                </span>
            </h2>
        </div>

        <div class="details space-y-6 sm:py-6 p-3.5">
            <div class="grid grid-cols-2 gap-4 sm:mb-12">
                <x-select.styled wire:model.live="type" label="Type of Purchase Order *" :options="['Meals', 'Travel', 'Ticket']" />
            </div>
            @if ($type === 'Meals')
                <div>
                    <p class="font-medium text-sm">Meals Form</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <x-select.styled wire:model="unit" label="Unit *" :options="[
                        ['label' => 'Pc', 'value' => 'pc'],
                        ['label' => 'Pack', 'value' => 'pack'],
                        ['label' => 'Sachet', 'value' => 'sachet'],
                        ['label' => 'Unit', 'value' => 'unit'],
                        ['label' => 'Ream', 'value' => 'ream'],
                        ['label' => 'Box', 'value' => 'box'],
                        ['label' => 'Set', 'value' => 'set'],
                        ['label' => 'Meter', 'value' => 'meter'],
                        ['label' => 'Kg', 'value' => 'kg'],
                        ['label' => 'Bag', 'value' => 'bag'],
                        ['label' => 'Case', 'value' => 'case'],
                        ['label' => 'Kit', 'value' => 'kit'],
                        ['label' => 'Lot', 'value' => 'lot'],
                        ['label' => 'Bucket', 'value' => 'bucket'],
                        ['label' => 'Galon', 'value' => 'galon'],
                        ['label' => 'Crate', 'value' => 'crate'],
                        ['label' => 'Bottle', 'value' => 'bottle'],
                        ['label' => 'Pax', 'value' => 'pax'],
                    ]" />
                    <x-number wire:model="pax_qty" label="No. of Pax *" />
                    <x-input wire:model="mealSnack" label="Meals / Snack *" />
                    <x-input wire:model="arrangement" label="Arrangement *" />
                    <x-date wire:model="delivery_date" format="DD [of] MMMM [of] YYYY" label="Delivery Date *" />
                    <div class="col-span-2">
                        <x-textarea wire:model="menu" resize-auto hint="Menu description" label="Menu *" />
                    </div>
                    <div class="col-span-1 gap-x-2">
                        <x-button icon="plus" position="left" wire:click="addMeals">Add Meal</x-button>
                        <x-button icon="printer" flat color="green" position="left"
                            wire:click="printPO">Print</x-button>
                    </div>
                </div>
            @elseif ($type === 'Travel')
                <div class="grid grid-cols-2 gap-x-4">
                    <x-select.styled wire:model.live="type" label="Some Travel Form *" :options="['Meals', 'Travel', 'Ticket']" />
                    <x-select.styled wire:model.live="type" label="Some Travel Form *" :options="['Meals', 'Travel', 'Ticket']" />
                </div>
            @endif
        </div>
    </div>
</div>
</div>

