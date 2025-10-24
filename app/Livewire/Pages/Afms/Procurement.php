<?php

namespace App\Livewire\Pages\Afms;

use App\Models\PurchaseRequest;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

class Procurement extends Component
{
    public $tab = 'Annual';

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.procurement');
    }

    #[On('procurement-tab')]
    public function changeTab($tab) {
        $this->tab = $tab;
    }

    #[On('alert')]
    public function alert($session)
    {
        return session()->flash('message', $session);
    }
}
