<?php

namespace App\Livewire\Pages\Afms;

use App\Models\Procurement;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ShowData extends Component
{

    public Procurement $procurement;

    public function mount($id) {
        $this->procurement = Procurement::with(['purchaseRequest', 'purchaseOrder'])->find( $id );
    }

    public function printRequest() {
        dd($this->procurement->purchaseRequest);
    }

    public function printOrder() {
        dd($this->procurement->purchaseOrder);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.show-data');
    }
}
