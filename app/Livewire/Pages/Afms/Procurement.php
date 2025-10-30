<?php

namespace App\Livewire\Pages\Afms;

use App\Models\Procurement as ModelsProcurement;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use League\Csv\Reader;
use League\Csv\Statement;
use Livewire\WithFileUploads;

class Procurement extends Component
{
    use WithFileUploads;

    public $tab = 'Annual';

    public $app_csv;

    public function readCSV() {
        $tempPath = $this->app_csv->storeAs('temp', $this->app_csv->getClientOriginalName(), 'public');
        $filePath = storage_path('app/public/' . $tempPath);

        $csv = Reader::from($filePath, 'r');
        $allRecords = iterator_to_array($csv->getRecords());


        $totalRows = count($allRecords);

        $chunkSize = 50000;
        $chunks = ceil($totalRows / $chunkSize);

        for($index = 0; $index < $chunks; $index++) {

            $statement = (new Statement())
                ->offset($index * $chunkSize)
                ->limit($chunkSize);

            $records = $statement->process($csv);

            $rows = iterator_to_array($records);

            DB::transaction(function () use ($rows) {
                    foreach($rows as $data) {
                        $annual = ModelsProcurement::create([
                            'code' => $data[0],
                            'project_title' => $data[1],
                            'pmo_end_user' => $data[2],
                            'early_activity' => strtolower($data[3]),
                            'mode_of_procurement' => $data[4],
                            'advertisement_posting' => $data[5],
                            'submission_bids' => $data[6],
                            'notice_of_award' => $data[7],
                            'contract_signing' => $data[8],
                            'source_of_funds' => $data[9],
                            'estimated_budget_total' => floatval(str_replace(',', '', $data[10] ?? 0)),
                            'estimated_budget_mooe' =>floatval(str_replace(',', '', $data[11] ?? 0)),
                        ]);
                    }
            });

        }

        $this->dispatch('modal:upload-close');
        return redirect()->route('pmu.index');
    }

    #[On('refresh-procurement-app')]
    public function refresh() {

    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.procurement');
    }

    #[On('procurement-tab')]
    public function changeTab($tab) {
        $this->tab = $tab;
    }

    #[On('alert')]
    public function alert($session)
    {
        return session()->flash('message', $session);
    }
}
