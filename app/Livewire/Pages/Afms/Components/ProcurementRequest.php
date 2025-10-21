<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Models\Procurement;
use App\Models\PurchaseRequest;
use Livewire\Attributes\Computed;
use Livewire\Component;


// bio
use Jmrashed\Zkteco\Lib\ZKTeco;

class ProcurementRequest extends Component
{
    public $tab = 'Requests';

    public ?string $search = null;
    public int $quantity = 5;

    public array $headers = [];

    public function mount()
    {
        $this->headers = [
            ['index' => 'procurement.code', 'label' => 'Code PAP'],
            ['index' => 'procurement.project_title', 'label' => 'Project Title'],
            ['index' => 'pr_number', 'label' => 'Pr Number'],
            ['index' => 'abc', 'label' => 'ABC'],
            ['index' => 'date_posted', 'label' => 'Date Posted'],
            ['index' => 'action', 'label' => 'Action'],
        ];
    }

    #[Computed()]
    public function rows() {
        return PurchaseRequest::query()
            ->with('procurement')
            ->when($this->search, function ($query) {
                $query->whereHas('procurement', function ($q) {
                    $q->where('project_title', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
                })->orWhere('pr_number', 'like', '%' . $this->search . '%');
            })
            ->orderBy('pr_number', 'asc')
            ->paginate($this->quantity)
            ->withQueryString();
    }

    public function submitToOrder(PurchaseRequest $request) {
       $request->purchaseOrder()->associate($request->id);
       $request->
    }

    public function render()
    {
        return view('livewire.pages.afms.components.procurement-request');
    }
}
