<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Models\Transaction;
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
            ['index' => 'action', 'label' => 'Generate RSMI'],
        ];
    }

    public function submitDate()
    {
        return $this->getTransactions();
    }

    #[Computed()]
    public function getTransactions()
    {
        if ($this->transactionDate) {
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

    public function createRsmi($stock_id, GenerateRsmiService $generate_rsmi_service)
    {
        $start = Carbon::parse($this->transactionDate[0])->startOfDay();
        $end = isset($this->transactionDate[1])
            ? Carbon::parse($this->transactionDate[1])->endOfDay()
            : $start->copy()->endOfDay();

        $this->rsmi = Transaction::with('requisition')->whereBetween('created_at', [$this->transactionDate[0], $end])->where('stock_id', $stock_id)->get();

        $fileName = $generate_rsmi_service->handle($this->rsmi, $this->transactionDate);

        return $this->dispatch('alert', [
            'text' => 'Report Generated successfully.',
            'color' => 'teal',
            'title' => 'Report of Supplies and Materials Issued'
        ]);
    }

    public function render()
    {
        return view('livewire.pages.afms.components.request-rsmi');
    }
}
