<?php

namespace App\Livewire\Pages\Afms;

use App\Actions\Procurement\ImportAppCsv;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Procurement extends Component
{
    use WithFileUploads;

    public $tab = 'Annual';

    public $app_csv;

    public function mount(): void
    {
        $this->authorize('manage-procurement');
    }

    public function readCSV(ImportAppCsv $importAppCsv) {
        $this->authorize('manage-procurement');

        $this->validate([
            'app_csv' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $tempPath = $this->app_csv->storeAs('temp', $this->app_csv->getClientOriginalName(), 'public');
        $filePath = storage_path('app/public/' . $tempPath);

        try {
            $result = $importAppCsv->handle($filePath);
        } catch (InvalidArgumentException $e) {
            session()->flash('message', ['title' => 'Upload failed', 'text' => $e->getMessage(), 'color' => 'red']);

            return redirect()->route('pmu.index');
        }

        session()->flash('message', [
            'title' => 'APP uploaded',
            'text' => sprintf(
                'APP %d: %d new and %d updated projects, total ABC ₱%s.',
                $result['year'], $result['created'], $result['updated'], number_format($result['total_budget'], 2)
            ),
            'color' => 'teal',
        ]);

        $this->dispatch('modal:upload-close');
        $this->dispatch('refresh-app');
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
