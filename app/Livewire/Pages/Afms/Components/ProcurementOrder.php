<?php

namespace App\Livewire\Pages\Afms\Components;

use Livewire\Component;

class ProcurementOrder extends Component
{
    public $tab = 'Orders';

    public array $headers = [];

    public function mount()
    {
        $this->headers = [];
    }

    public function render()
    {
        return view('livewire.pages.afms.components.procurement-order');
    }
}
