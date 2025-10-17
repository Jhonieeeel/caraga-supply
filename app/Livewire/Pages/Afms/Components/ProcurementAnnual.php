<?php

namespace App\Livewire\Pages\Afms\Components;

use Livewire\Component;

class ProcurementAnnual extends Component
{

    public $tab = 'Annual';

    public array $headers = [];


    public function mount()
    {
        $this->headers = [];
    }

    public function render()
    {
        return view('livewire.pages.afms.components.procurement-annual');
    }
}
