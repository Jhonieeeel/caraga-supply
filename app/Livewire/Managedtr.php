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

    public function mount()
    {
        $this->headers = [
            ['index' => 'Holiday', 'label' => 'Holiday'],
            ['index' => 'Date', 'label' => 'Date'],
            ['index' => 'Action', 'label' => 'Action'],
        ];
    }


    
    
    #[Layout('layouts.app')]
    public function remove($index)
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows); // reindex array
    }


}
