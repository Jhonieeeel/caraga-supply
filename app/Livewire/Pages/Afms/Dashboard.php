<?php

namespace App\Livewire\Pages\Afms;

use App\Models\Requisition;
use App\Models\Stock;
use App\Models\Supply;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Dashboard extends Component
{

    #[Computed()]
    public function requests()
    {
        return Requisition::whereDate('created_at', Carbon::today())  // or now()
            ->get();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.dashboard', [
            'supplies' => Supply::count(),
            'stocks' => Stock::where('quantity', '>=', 1)->count(),
            'requisitions' => Requisition::where('completed', false)->count()
        ]);
    }
}
