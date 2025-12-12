<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Models\Stock;
use App\Models\Transaction;
use App\Services\Afms\GenerateRpciService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RequestRpci extends Component
{

    // RPCI STOCK CARD

    public array $headers = [];
    public int $quantity = 5;

    public Collection $rpci;

    public $transactionDate;

    public function mount()
    {
        $this->headers = [
            ['index' => 'stock.stock_number', 'label' => 'Stock Number'],
            ['index' => 'stock.supply.name', 'label' => 'Stock Name'],
            ['index' => 'action', 'label' => 'Generate RSMI'],
        ];
    }

    #[Computed()]
    public function totalRows() {
        if ($this->transactionDate) {
            $start = Carbon::parse($this->transactionDate[0])->startOfDay();

            $end = isset($this->transactionDate[1])
                ? Carbon::parse($this->transactionDate[1])->endOfDay()
                : $start->copy()->endOfDay();


            return true;
        }
    }

    #[Computed()]
    public function rows()
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

    public function submitDate(GenerateRpciService $generate_rpci)
    {
        return $this->createRpci($generate_rpci);
    }

    public function createRpci(GenerateRpciService $generate_rpci) {
        $start = Carbon::parse($this->transactionDate[0])->startOfDay();
        $end = isset($this->transactionDate[1])
            ? Carbon::parse($this->transactionDate[1])->endOfDay()
            : $start->copy()->endOfDay();


        $stockDetails = Stock::with(['transactions'])->get()->map(function($stock) {

            $poTransactions = $stock->transactions->where('type_of_transaction', 'PO');
            $risTransactions = $stock->transactions->where('type_of_transaction', 'RIS');

            return [
                    'stock_id' => $stock->id,
                    'stock_name' => $stock->supply->name,
                    'stock_number' => $stock->stock_number,
                    'unit_measure' => $stock->supply->unit,
                    'unit_value' => $stock->price,
                    'po' => [
                        'transactions' => $poTransactions->toArray(),
                        'total_quantity' => $poTransactions->sum('quantity'),
                    ],
                    'ris' => [
                        'transactions' => $risTransactions->toArray(),
                        'total_quantity' => $risTransactions->sum('quantity'),
                    ],
                    'net_quantity' => $poTransactions->sum('quantity') - $risTransactions->sum('quantity'),
            ];
            });

        return $generate_rpci->handle($stockDetails->toArray());
    }
    // public function createRpci(GenerateRpciService $generate_rpci, $stock_id)
    // {
    //     // date
    //     $start = Carbon::parse($this->transactionDate[0])->startOfDay();
    //     $end = isset($this->transactionDate[1])
    //         ? Carbon::parse($this->transactionDate[1])->endOfDay()
    //         : $start->copy()->endOfDay();

    //     // transactions
    //     $this->rpci = Transaction::with('requisition')->whereBetween('created_at', [$this->transactionDate[0], $end])->where('stock_id', $stock_id)->get();

    //     $fileName = $generate_rpci->handle($this->rpci, $stock_id);

    //     return $this->dispatch('alert', [
    //         'text' => 'Report Generated successfully.',
    //         'color' => 'teal',
    //         'title' => 'Report of Supplies and Materials Issued'
    //     ]);
    // }

    public function render()
    {
        return view('livewire.pages.afms.components.request-rpci');
    }
}
