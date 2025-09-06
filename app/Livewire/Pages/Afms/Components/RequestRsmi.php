<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Transaction;
use App\Services\Afms\GenerateRpciService;
use App\Services\Afms\GenerateRsmiService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class RequestRsmi extends Component
{
    use WithPagination;

    public array $headers = [];

    public Collection $rsmi;
    public $transactionDate;
    public $rsmiSearch;

    public $transactions;

    public function mount()
    {
        $this->headers = [
            ['index' => 'stock.stock_number', 'label' => 'Stock Number'],
            ['index' => 'stock.supply.name', 'label' => 'Stock Name'],
            ['index' => 'action'],
        ];
    }

    public function submitDate() {
        return $this->getTransactions();
    }

    #[Computed()]
    public function getTransactions()
    {
        if($this->transactionDate) {
            $start = Carbon::parse($this->transactionDate[0])->startOfDay();

            $end = isset($this->transactionDate[1])
                ? Carbon::parse($this->transactionDate[1])->endOfDay()
                : $start->copy()->endOfDay();

            return Transaction::select('stock_id')
                ->distinct()
                ->with(['requisition', 'stock'])
                ->whereBetween('created_at', [$start, $end])
                ->paginate(5)
                ->withQueryString();

        }

        return [];
    }

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
        if ($this->transactionDate) {
            $start = Carbon::parse($this->transactionDate[0])->startOfDay();

            $end = isset($this->transactionDate[1])
                ? Carbon::parse($this->transactionDate[1])->endOfDay()
                : $start->copy()->endOfDay();

            return $items = RequisitionItem::with(['stock.supply', 'requisition'])
                ->whereHas('requisition', function ($query) use ($start, $end) {
                    $query->whereBetween('updated_at', [$start, $end])->where('completed', true);
                })
                ->select('stock_id')
                ->distinct()
                ->paginate(5)
                ->map(fn($item) => [
                    'name' => $item->stock->supply->name ?? 'N/A',
                    'stock_id' => $item->stock_id,
                    'stock_number'  => $item->stock->stock_number ?? 'N/A',
                ]);
        }

        return [];
    }

    public function createRsmi($stock_id, GenerateRsmiService $generate_rsmi_service)
    {
        $end = Carbon::parse($this->transactionDate[1])->addDay();

        $this->rsmi = Transaction::whereBetween('created_at', [$this->transactionDate[0], $end])->where('stock_id', $stock_id)->get();

        dd($this->rsmi);

        // $this->rsmi = Requisition::with('items.stock')
        //     ->where('completed', true)
        //     ->whereBetween('created_at', [$this->transactionDate[0], $end])
        //     ->whereHas('items', function ($query) use ($stock_id) {
        //         $query->where('stock_id', $stock_id);
        //     })
        //     ->get();

        $fileName = $generate_rsmi_service->handle($this->rsmi, $this->transactionDate);

        return;
    }

    public function render()
    {
        return view('livewire.pages.afms.components.request-rsmi');
    }
}
