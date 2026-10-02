<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\User;

class Managedtr extends Component
{
    public $headers = [];
    public $rows = [];

    public $dtrFile;

    // holiday inputs
    public $newHoliday = '';
    public $newDate = '';

    // signatory inputs
    public $signatoryName = '';
    public $signatoryPosition = '';
    public $signatories = [];

    public function mount()
    {
        $this->headers = [
            ['index' => 'Holiday', 'label' => 'Holiday'],
            ['index' => 'Date', 'label' => 'Date'],
            ['index' => 'Action', 'label' => 'Action'],
        ];
    }


    public function addHoliday()
    {
        $this->validate([
            'newHoliday' => 'required|string|max:255',
            'newDate' => 'required|string',
        ]);


        $this->rows[] = [
            'holiday' => $this->newHoliday,
            'date' => $this->newDate,
        ];

        // $this->newHoliday = '';
        // $this->newDate = '';
    }
    public function AddSignatory()
    {
        $this->validate([
            'signatoryName' => 'required|string|max:255',
            'signatoryPosition' => 'required|string|max:255',
        ]);

        $this->signatories[] = [
            'Name' => $this->signatoryName,
            'Designation' => $this->signatoryPosition,
        ];

        // Clear inputs
        $this->reset(['signatoryName', 'signatoryPosition']);
    }

    public function removeSignatory($index)
    {
        unset($this->signatories[$index]);
        $this->signatories = array_values($this->signatories);
    }

    public function remove($index)
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows); // reindex array
    }

    #[Layout('layouts.app')]
    public function render()
    {
        // the view file is "Managedtr.blade.php" (capital M); name it explicitly
        // so it also resolves on case-sensitive filesystems
        return view('livewire.Managedtr');
    }
}
