@php
    $money = fn ($value) => is_null($value) ? '' : number_format($value, 2);
    $statusColor = ['Not Yet Started' => 'gray', 'Ongoing' => 'yellow', 'Completed' => 'teal'];
    $totals = $this->totals;
@endphp

<div class="space-y-6">
    <style>
        @media print {
            @page { size: landscape; margin: 8mm; }
            body * { visibility: hidden; }
            #pmr-report, #pmr-report * { visibility: visible; }
            #pmr-report { position: absolute; left: 0; top: 0; width: 100%; }
            #pmr-report .pmr-scroll { overflow: visible !important; }
            #pmr-report table { font-size: 7px; }
            .pmr-no-print { display: none !important; }
        }
    </style>

    <div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8 bg-white border shadow rounded">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 pb-3 sm:p-6 p-3 pmr-no-print">
            <h2 class="text-xl font-semibold text-gray-800 tracking-tight">
                Procurement Monitoring Report (PMR)
            </h2>
            <div class="flex gap-2">
                <x-button x-on:click="window.print()" color="gray" icon="printer" position="left" outline>
                    Print
                </x-button>
                <x-button wire:click="exportCsv" color="green" icon="arrow-down-tray" position="left">
                    Export CSV
                </x-button>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3 p-3 sm:px-6 pmr-no-print">
            <x-select.styled wire:model.live="year" label="APP Year" :options="$this->years" placeholder="All years" />
            <x-select.styled wire:model.live="status" label="Status" placeholder="All statuses"
                :options="collect(\App\Livewire\Pages\Afms\Pmr::STATUSES)->map(fn ($s) => ['label' => $s, 'value' => $s])->all()" />
            <x-input wire:model.live.debounce.400ms="search" label="Search" placeholder="Code, project, or end-user"
                icon="magnifying-glass" />
        </div>

        <div id="pmr-report" class="p-3 sm:p-6 space-y-4">
            <div class="text-center">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-600">Office of Civil Defense - Caraga Region</p>
                <h1 class="text-lg font-bold uppercase text-gray-900">Procurement Monitoring Report</h1>
                <p class="text-sm text-gray-600">{{ $year ? "Calendar Year {$year}" : 'All years' }}</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <div class="rounded border p-3">
                    <p class="text-xs text-gray-500">Total ABC</p>
                    <p class="text-lg font-bold">₱{{ number_format($totals['abc'], 2) }}</p>
                </div>
                <div class="rounded border p-3">
                    <p class="text-xs text-gray-500">Total Contract Cost</p>
                    <p class="text-lg font-bold">₱{{ number_format($totals['contract_cost'], 2) }}</p>
                </div>
                <div class="rounded border p-3">
                    <p class="text-xs text-gray-500">Savings (awarded)</p>
                    <p class="text-lg font-bold">₱{{ number_format($totals['savings'], 2) }}</p>
                </div>
                @foreach ($totals['counts'] as $label => $count)
                    <div class="rounded border p-3">
                        <p class="text-xs text-gray-500">{{ $label }}</p>
                        <p class="text-lg font-bold">{{ $count }}</p>
                    </div>
                @endforeach
            </div>

            <div class="pmr-scroll overflow-x-auto border rounded">
                <table class="min-w-full text-xs border-collapse">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th rowspan="2" class="border px-2 py-1">Code</th>
                            <th rowspan="2" class="border px-2 py-1">Program/Project</th>
                            <th rowspan="2" class="border px-2 py-1">PMO/ End-User</th>
                            <th rowspan="2" class="border px-2 py-1">Mode of Procurement</th>
                            <th colspan="2" class="border px-2 py-1">Schedule per APP</th>
                            <th colspan="7" class="border px-2 py-1">Actual Procurement Activity</th>
                            <th rowspan="2" class="border px-2 py-1">Source of Funds</th>
                            <th colspan="3" class="border px-2 py-1">ABC (PhP)</th>
                            <th rowspan="2" class="border px-2 py-1">Contract Cost (PhP)</th>
                            <th rowspan="2" class="border px-2 py-1">Savings (PhP)</th>
                            <th rowspan="2" class="border px-2 py-1">Supplier</th>
                            <th rowspan="2" class="border px-2 py-1">Status</th>
                            <th rowspan="2" class="border px-2 py-1">Remarks</th>
                        </tr>
                        <tr>
                            <th class="border px-2 py-1">Start of Procurement Activity</th>
                            <th class="border px-2 py-1">End of Procurement Activity</th>
                            <th class="border px-2 py-1">PR No.</th>
                            <th class="border px-2 py-1">Ads/Post of IB</th>
                            <th class="border px-2 py-1">Sub/Open of Bids</th>
                            <th class="border px-2 py-1">Notice of Award</th>
                            <th class="border px-2 py-1">PO Date</th>
                            <th class="border px-2 py-1">Notice to Proceed</th>
                            <th class="border px-2 py-1">Delivery/ Completion</th>
                            <th class="border px-2 py-1">Total</th>
                            <th class="border px-2 py-1">MOOE</th>
                            <th class="border px-2 py-1">CO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->rows as $row)
                            <tr wire:key="pmr-{{ $row['id'] }}" class="align-top hover:bg-gray-50">
                                <td class="border px-2 py-1 whitespace-nowrap">
                                    <a href="{{ route('pmu.show', $row['id']) }}" wire:navigate class="text-primary-600 hover:underline">{{ $row['code'] }}</a>
                                </td>
                                <td class="border px-2 py-1 min-w-48">{{ $row['project_title'] }}</td>
                                <td class="border px-2 py-1">{{ $row['pmo_end_user'] }}</td>
                                <td class="border px-2 py-1">{{ $row['mode_of_procurement'] }}</td>
                                <td class="border px-2 py-1 whitespace-nowrap">{{ $row['plan_start'] }}</td>
                                <td class="border px-2 py-1 whitespace-nowrap">{{ $row['plan_end'] }}</td>
                                <td class="border px-2 py-1 whitespace-nowrap">{{ $row['pr_number'] }}</td>
                                <td class="border px-2 py-1 whitespace-nowrap">{{ $row['act_ads_post'] }}</td>
                                <td class="border px-2 py-1 whitespace-nowrap">{{ $row['act_sub_open'] }}</td>
                                <td class="border px-2 py-1 whitespace-nowrap">{{ $row['act_noa'] }}</td>
                                <td class="border px-2 py-1 whitespace-nowrap">{{ $row['act_po_date'] }}</td>
                                <td class="border px-2 py-1 whitespace-nowrap">{{ $row['act_ntp'] }}</td>
                                <td class="border px-2 py-1 whitespace-nowrap">{{ $row['act_delivery'] }}</td>
                                <td class="border px-2 py-1">{{ $row['source_of_funds'] }}</td>
                                <td class="border px-2 py-1 text-right whitespace-nowrap">{{ $money($row['abc_total']) }}</td>
                                <td class="border px-2 py-1 text-right whitespace-nowrap">{{ $money($row['abc_mooe']) }}</td>
                                <td class="border px-2 py-1 text-right whitespace-nowrap">{{ $money($row['abc_co']) }}</td>
                                <td class="border px-2 py-1 text-right whitespace-nowrap">{{ $money($row['contract_cost']) }}</td>
                                <td class="border px-2 py-1 text-right whitespace-nowrap">{{ $money($row['savings']) }}</td>
                                <td class="border px-2 py-1">{{ $row['supplier'] }}</td>
                                <td class="border px-2 py-1 whitespace-nowrap">
                                    <x-badge :color="$statusColor[$row['status']]" :text="$row['status']" flat />
                                </td>
                                <td class="border px-2 py-1 min-w-40">{{ $row['remarks'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="22" class="border px-2 py-6 text-center text-gray-500">No procurement records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($this->rows->isNotEmpty())
                        <tfoot class="bg-gray-50 font-semibold">
                            <tr>
                                <td colspan="14" class="border px-2 py-1 text-right">Total</td>
                                <td class="border px-2 py-1 text-right whitespace-nowrap">{{ number_format($totals['abc'], 2) }}</td>
                                <td class="border px-2 py-1 text-right whitespace-nowrap">{{ number_format($this->rows->sum('abc_mooe'), 2) }}</td>
                                <td class="border px-2 py-1 text-right whitespace-nowrap">{{ number_format($this->rows->sum('abc_co'), 2) }}</td>
                                <td class="border px-2 py-1 text-right whitespace-nowrap">{{ number_format($totals['contract_cost'], 2) }}</td>
                                <td class="border px-2 py-1 text-right whitespace-nowrap">{{ number_format($totals['savings'], 2) }}</td>
                                <td colspan="3" class="border px-2 py-1"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
