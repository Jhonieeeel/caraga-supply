<?php

namespace App\Livewire\Pages\Afms;

use Livewire\Attributes\Layout;
use Livewire\Component;

class StockTable extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.stock-table');
    }
}
