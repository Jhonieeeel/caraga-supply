<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Models\Transaction;
use App\Services\Afms\GenerateRsmiService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class RequestRsmi extends Component
{
    use WithPagination;

    public array $headers = [];
    public array $fileHeaders = [];

    public Collection $rsmi;

    public ?Transaction $transaction = null;
    public $transactionDate;
    public $rsmiSearch;

    public $transactions;

    public function mount()
    {
        $this->headers = [
            ['index' => 'stock.stock_number', 'label' => 'Stock Number'],
            ['index' => 'stock.supply.name', 'label' => 'Stock Name'],
            ['index' => 'action', 'label' => 'Generate/Download'],
        ];

        $this->fileHeaders = [
            ['index' => 'updated_at', 'label' => 'Date Generated'],
            ['action' => 'action', 'label' => 'Download']
        ];
    }

    public function downloadRsmi($file) {
        return $this->downloadGeneratedFile($file);
    }

    /**
     * Only files this module generated (recorded on a transaction, under
     * rsmi/ on the public disk) can be downloaded; the path comes from the
     * client, so never hand it straight to the filesystem.
     */
    protected function downloadGeneratedFile($file)
    {
        $this->authorize('approve-requisition');

        $file = (string) $file;

        abort_unless(
            str_starts_with($file, 'rsmi/')
                && ! str_contains($file, '..')
                && Transaction::where('rsmi_file', $file)->exists()
                && Storage::disk('public')->exists($file),
            404
        );

        return Storage::disk('public')->download($file);
    }

    public function submitDate()
    {
        return $this->getTransactions();
    }
    #[Computed()]
    public function getGeneratedFiles() {
        if ($this->transaction) {
            return Transaction::where('stock_id', '=' , $this->transaction->stock_id)
            ->where('rsmi_file', '!=', null);
        }

        return [];
    }


    #[Computed()]
    public function getTransactions()
    {
        if ($this->transactionDate) {
            $start = Carbon::parse($this->transactionDate[0])->startOfDay();

            $end = isset($this->transactionDate[1])
                ? Carbon::parse($this->transactionDate[1])->endOfDay()
                : $start->copy()->endOfDay();

            // One row per stock, carrying what the view needs: an id for the
            // button label and the latest generated RSMI file (file names are
            // timestamped, so MAX() is the newest) for the download button.
            return Transaction::query()
                    ->select('stock_id')
                    ->selectRaw('MAX(id) as id')
                    ->selectRaw('MAX(rsmi_file) as rsmi_file')
                    ->with(['stock.supply'])
                    ->whereBetween('created_at', [$start, $end])
                    ->groupBy('stock_id')
                    ->paginate(5)
                    ->withQueryString();


        }

        return [];
    }

    #[On('downloadRsmi')]
    public function autoDownload($file) {
        return $this->downloadGeneratedFile($file);
    }


    public function createRsmi($stock_id, GenerateRsmiService $generate_rsmi_service)
    {
        $this->authorize('approve-requisition');

        $start = Carbon::parse($this->transactionDate[0])->startOfDay();
        $end = isset($this->transactionDate[1])
            ? Carbon::parse($this->transactionDate[1])->endOfDay()
            : $start->copy()->endOfDay();

        $this->rsmi = Transaction::with('requisition')->whereBetween('created_at', [$this->transactionDate[0], $end])->where('stock_id', $stock_id)->get();

        $file = $generate_rsmi_service->handle($this->rsmi, $this->transactionDate, $this->rsmi->first());

        // download file optional
        $this->dispatch('downloadRsmi', $file);

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
