<?php

namespace App\Livewire\Pages\Afms\Components;

use Livewire\Component;

class ProcurementRequest extends Component
{
    public $tab = 'Requests';

    public array $headers = [];

    public function mount()
    {
        $this->headers = [];
    }


    public function render()
    {
        return view('livewire.pages.afms.components.procurement-request');
    }
}
