<?php

namespace App\Livewire;

use Livewire\Component;

class signatory extends Component
{
    public $headers = [];
    public $rows = []; 
    public $signatoryName = '';
    public $signatoryPosition = '';
    public $signatories = [];
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

    public function render()
    {
        return view('livewire.signatory');
    }
}
