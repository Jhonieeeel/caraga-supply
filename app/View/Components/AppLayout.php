<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Log;
use Illuminate\View\Component;
use Illuminate\View\View;
use Livewire\Attributes\On;
use TallStackUi\Traits\Interactions;

class AppLayout extends Component
{
    use Interactions;


    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
