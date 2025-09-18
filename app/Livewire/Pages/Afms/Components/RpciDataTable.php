<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Models\Transaction;
use App\Services\Afms\ReadRpciService;
use Livewire\Component;

class RpciDataTable extends Component
{
    public array $headers = [];

    public function mount()
    {
        $this->headers = [
            ['index' => 'stock_number', 'label' => 'Stock ID'],

            ['index' => 'action'],
        ];
    }

    public function submit(ReadRpciService $read)
    {
        $read->handle("Supplies-2025-01");
        return;
    }

    public function rows()
    {
        return Transaction::where('created_at',);
    }

    public function render()
    {
        return view('livewire.pages.afms.components.rpci-data-table');
    }
}
