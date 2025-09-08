<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Models\Transaction;
use App\Services\Afms\GenerateRpciService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RequestRpci extends Component
{
    public array $headers = [];
    public int $quantity = 5;

    public Collection $rpci;

    public $transactionDate;

    public function mount()
    {
        $this->headers = [
            ['index' => 'stock.stock_number', 'label' => 'Stock Number'],
            ['index' => 'stock.supply.name', 'label' => 'Stock Name'],
            ['index' => 'action'],
        ];
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

    public function submitDate()
    {
        return $this->rows();
    }

    public function createRpci(GenerateRpciService $generate_rpci, $stock_id)
    {
        // date 
        $start = Carbon::parse($this->transactionDate[0])->startOfDay();
        $end = isset($this->transactionDate[1])
            ? Carbon::parse($this->transactionDate[1])->endOfDay()
            : $start->copy()->endOfDay();

        // transactions
        $this->rpci = Transaction::with('requisition')->whereBetween('created_at', [$this->transactionDate[0], $end])->where('stock_id', $stock_id)->get();

        $fileName = $generate_rpci->handle($this->rpci, $stock_id);

        return $this->dispatch('alert', [
            'text' => 'Report Generated successfully.',
            'color' => 'teal',
            'title' => 'Report of Supplies and Materials Issued'
        ]);
    }

    public function render()
    {
        return view('livewire.pages.afms.components.request-rpci');
    }
}
