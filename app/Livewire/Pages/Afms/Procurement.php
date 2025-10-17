<?php

namespace App\Livewire\Pages\Afms;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Procurement extends Component
{
    public $tab = 'Annual';

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.procurement');
    }
}
