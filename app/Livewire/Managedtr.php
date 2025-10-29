<?php

namespace App\Livewire;

<<<<<<< HEAD
<<<<<<< HEAD
use Livewire\Component;

class Managedtr extends Component
{
    public function render()
    {
        return view('livewire.manage-dtr');
    }
}
=======
=======
>>>>>>> 694fd663cd32485ddea5fe9c9d3e5398541cb59b
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\User;

class Managedtr extends Component
{   
    public $headers = [];
    public $rows = []; 

    // Bind inputs
    public $newHoliday = '';
    public $newDate = '';
    public $showUploadModal = false;
    public $showSignatoryModal = false;

    public $dtrFile;
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

        $this->newHoliday = '';
        $this->newDate = '';
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

    #[Layout('layouts.app')]
    public function remove($index)
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows); // reindex array
    }


<<<<<<< HEAD
}
>>>>>>> 694fd663cd32485ddea5fe9c9d3e5398541cb59b
=======
}
>>>>>>> 694fd663cd32485ddea5fe9c9d3e5398541cb59b
