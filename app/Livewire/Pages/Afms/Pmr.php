<?php

namespace App\Livewire\Pages\Afms;

use App\Models\Procurement;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Pmr extends Component
{
    use WithPagination;

    public const STATUSES = ['Not Yet Started', 'Ongoing', 'Completed'];

    public const PER_PAGE = 10;

    public ?int $year = null;
    public ?string $status = null;
    public ?string $search = null;

    public function mount(): void
    {
        $this->authorize('manage-procurement');

        $this->year = Procurement::max('app_year') ?? (int) now()->year;
    }

    public function updated($property): void
    {
        if (in_array($property, ['year', 'status', 'search'], true)) {
            $this->resetPage();
        }
    }

    /**
     * The rows shown on screen. Status is derived per row, so the filtered
     * collection is paged in memory; totals, print and CSV still use all rows.
     */
    #[Computed()]
    public function pageRows(): LengthAwarePaginator
    {
        $rows = $this->rows;
        $page = min($this->getPage(), max(1, (int) ceil($rows->count() / self::PER_PAGE)));

        return new LengthAwarePaginator(
            $rows->forPage($page, self::PER_PAGE)->values(),
            $rows->count(),
            self::PER_PAGE,
            $page,
            ['path' => route('pmr.index'), 'pageName' => 'page'],
        );
    }

    /**
     * Not Yet Started: no PR yet. Ongoing: has a PR and/or PO. Completed: the PO has a delivery date.
     */
    public static function statusOf(Procurement $procurement): string
    {
        if ($procurement->purchaseOrder?->delivery_date) {
            return 'Completed';
        }

        if ($procurement->purchaseRequest || $procurement->purchaseOrder) {
            return 'Ongoing';
        }

        return 'Not Yet Started';
    }

    #[Computed()]
    public function years(): array
    {
        return Procurement::whereNotNull('app_year')
            ->distinct()
            ->orderByDesc('app_year')
            ->pluck('app_year')
            ->map(fn ($year) => ['label' => (string) $year, 'value' => (int) $year])
            ->all();
    }

    #[Computed()]
    public function rows(): Collection
    {
        return Procurement::query()
            ->with(['purchaseRequest', 'purchaseOrder'])
            ->when($this->year, fn ($query) => $query->where('app_year', $this->year))
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('code', 'like', "%{$this->search}%")
                        ->orWhere('project_title', 'like', "%{$this->search}%")
                        ->orWhere('pmo_end_user', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('code')
            ->get()
            ->map(fn (Procurement $procurement) => $this->toRow($procurement))
            ->when(filled($this->status), fn (Collection $rows) => $rows->where('status', $this->status)->values());
    }

    #[Computed()]
    public function totals(): array
    {
        $rows = $this->rows;
        $awarded = $rows->whereNotNull('contract_cost');

        return [
            'abc' => $rows->sum('abc_total'),
            'contract_cost' => $awarded->sum('contract_cost'),
            'savings' => $awarded->sum('savings'),
            'counts' => collect(self::STATUSES)->mapWithKeys(fn ($status) => [$status => $rows->where('status', $status)->count()])->all(),
        ];
    }

    private function toRow(Procurement $procurement): array
    {
        $pr = $procurement->purchaseRequest;
        $po = $procurement->purchaseOrder;

        $abcTotal = $procurement->estimated_budget_total ?? $procurement->total_abc;
        $abcTotal = is_null($abcTotal) ? null : (float) $abcTotal;
        $contractCost = is_null($po?->contract_price) ? null : (float) $po->contract_price;

        return [
            'id' => $procurement->id,
            'code' => $procurement->code,
            'project_title' => $procurement->project_title,
            'pmo_end_user' => $procurement->pmo_end_user,
            'mode_of_procurement' => $procurement->mode_of_procurement,
            // APP schedule (planned, free text as entered in the APP, e.g. "2nd Quarter 2026")
            'plan_start' => $procurement->procurement_start,
            'plan_end' => $procurement->procurement_end,
            // actual activity (from the PR and PO)
            'pr_number' => $pr?->pr_number,
            'act_ads_post' => $pr?->date_posted?->format('M d, Y'),
            'act_sub_open' => $pr?->closing_date?->format('M d, Y'),
            'act_noa' => $po?->noa?->format('M d, Y'),
            'act_po_date' => $po?->po_date?->format('M d, Y'),
            'act_ntp' => $po?->ntp?->format('M d, Y'),
            'act_delivery' => $po?->delivery_date?->format('M d, Y'),
            'source_of_funds' => $procurement->source_of_funds,
            'abc_total' => $abcTotal,
            'abc_mooe' => is_null($procurement->estimated_budget_mooe) ? null : (float) $procurement->estimated_budget_mooe,
            'abc_co' => is_null($procurement->estimated_budget_co) ? null : (float) $procurement->estimated_budget_co,
            'contract_cost' => $contractCost,
            'savings' => (! is_null($abcTotal) && ! is_null($contractCost)) ? $abcTotal - $contractCost : null,
            'supplier' => $po?->supplier,
            'status' => self::statusOf($procurement),
            'remarks' => $procurement->remarks,
        ];
    }

    /**
     * Column key => header, in the order shown on screen and in the CSV.
     */
    public static function columns(): array
    {
        return [
            'code' => 'Code',
            'project_title' => 'Program/Project',
            'pmo_end_user' => 'PMO/End-User',
            'mode_of_procurement' => 'Mode of Procurement',
            'plan_start' => 'APP: Start of Procurement Activity',
            'plan_end' => 'APP: End of Procurement Activity',
            'pr_number' => 'PR No.',
            'act_ads_post' => 'Actual: Ads/Post of IB',
            'act_sub_open' => 'Actual: Sub/Open of Bids',
            'act_noa' => 'Actual: Notice of Award',
            'act_po_date' => 'Actual: PO Date',
            'act_ntp' => 'Actual: Notice to Proceed',
            'act_delivery' => 'Actual: Delivery/Completion',
            'source_of_funds' => 'Source of Funds',
            'abc_total' => 'ABC Total (PhP)',
            'abc_mooe' => 'ABC MOOE (PhP)',
            'abc_co' => 'ABC CO (PhP)',
            'contract_cost' => 'Contract Cost (PhP)',
            'savings' => 'Savings (PhP)',
            'supplier' => 'Supplier',
            'status' => 'Status',
            'remarks' => 'Remarks',
        ];
    }

    public function exportCsv(): StreamedResponse
    {
        $this->authorize('manage-procurement');

        $columns = self::columns();
        $rows = $this->rows;
        $title = 'OCD Caraga - Procurement Monitoring Report (PMR)' . ($this->year ? " CY {$this->year}" : '');

        return response()->streamDownload(function () use ($columns, $rows, $title) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel reads ₱ and ñ correctly
            fputcsv($out, [$title], escape: '');
            fputcsv($out, array_values($columns), escape: '');

            foreach ($rows as $row) {
                fputcsv($out, array_map(fn ($key) => $row[$key], array_keys($columns)), escape: '');
            }

            fclose($out);
        }, 'PMR' . ($this->year ? "-{$this->year}" : '') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.pmr');
    }
}
