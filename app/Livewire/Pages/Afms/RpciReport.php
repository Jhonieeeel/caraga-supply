<?php

namespace App\Livewire\Pages\Afms;

use Livewire\Attributes\Layout;
use Livewire\Component;

class RpciReport extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.rpci-report');
    }
}
