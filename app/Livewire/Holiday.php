<?php

namespace App\Livewire;

use Livewire\Component;

class Holiday extends Component
{
    
    public $headers = [];
    public $rows = []; 

    // Bind inputs
    public $newHoliday = '';
    public $newDate = '';
    public $showUploadModal = false;
    public $showSignatoryModal = false;
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
    public function render()
    {
        return view('livewire.holiday');
    }
}
