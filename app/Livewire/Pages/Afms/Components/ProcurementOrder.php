<?php

namespace App\Livewire\Pages\Afms\Components;

use Livewire\Attributes\Computed;
use Livewire\Component;

class ProcurementOrder extends Component
{
    public $tab = 'Orders';

    public array $headers = [];

    public function mount()
    {
        $this->headers = [
            ['index' => 'procurement.code' ,'label'=> 'Code PAP'],
            ['index' => 'procurement.project_title' ,'label'=> 'Project Title'],
            ['index' => 'po_number' ,'label'=> 'PO Number'],
            ['index' => 'supplier' ,'label'=> 'Supplier'],
            ['index' => 'procurement.mode_of_procurement' ,'label'=> 'Mode of Procurement'],
            ['index' => 'action' ,'label'=> 'Action'],
        ];
    }

    #[Computed()]
    public function rows() {
        // Implement the logic to fetch procurement order rows
        return []; // Placeholder return
    }

    public function render()
    {
        return view('livewire.pages.afms.components.procurement-order');
    }
}
