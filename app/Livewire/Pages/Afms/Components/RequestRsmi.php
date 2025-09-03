<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Services\Afms\GenerateRpciService;
use App\Services\Afms\GenerateRsmiService;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RequestRsmi extends Component
{

    public array $rsmi = [];
    public $rsmiDate;
    public $rsmiSearch;

    // RSMI
    #[Computed()]
    public function getRSMI()
    {
        return RequisitionItem::with('requisition')
            ->whereHas('requisition', function ($query) {
                $query->whereCompleted('true');
            })
            ->distinct('stock_id')
            ->get()
            ->load('stock');
    }

    #[Computed()]
    public function getSupplies()
    {
        if ($this->rsmiDate) {
            $start = Carbon::parse($this->rsmiDate[0]);
            $end = Carbon::parse($this->rsmiDate[1])->addDay();

            return RequisitionItem::with(['stock.supply'])
                ->whereHas('requisition', function ($query) use ($start, $end) {
                    $query->where('completed', true)
                        ->whereBetween('created_at', [$start, $end]);
                })
                ->select('stock_id')
                ->distinct()
                ->get()
                ->map(fn($item) => [
                    'label' => $item->stock->supply->name ?? 'N/A',
                    'value' => $item->stock_id,
                    'note'  => $item->stock->stock_number ?? 'N/A',
                ]);
        }

        return [];
    }

    public function createRsmi(GenerateRsmiService $generate_rsmi_service)
    {
        $end = Carbon::parse($this->rsmiDate[1])->addDay();

        $this->rsmi = Requisition::with('items.stock')
            ->where('completed', true)
            ->whereBetween('created_at', [$this->rsmiDate[0], $end])
            ->whereHas('items', function ($query) {
                $query->where('stock_id', $this->rsmiSearch);
            })
            ->get();

        $generate_rsmi_service->handle($this->rsmi, $this->rsmiDate);
    }

    public function render()
    {
        return view('livewire.pages.afms.components.request-rsmi');
    }
}
