<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Actions\Procurement\CreateRequest;
use App\Actions\Procurement\UpdateRequest;
use App\Livewire\Forms\RequestForm;
use App\Models\Procurement;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProcurementRequest extends Component
{
    use WithFileUploads;
    public $tab = 'Requests';

    public RequestForm $requestForm;
    public PurchaseRequest $purchaseRequest;

    public ?string $search = null;
    public int $quantity = 5;

    // fks
    public ?int $procurement_id = null;
    public array $headers = [];

    public function mount()
    {
        $this->headers = [
            ['index' => 'pr_number', 'label' => 'PR Number'],
            ['index' => 'procurement.code', 'label' => 'Code PAP'],
            ['index' => 'procurement.project_title', 'label' => 'Project Title'],
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

    public function onUpdate(UpdateRequest $updateRequest, PurchaseRequest $purchaseRequest) {

        $res = $this->requestForm->update($updateRequest, $purchaseRequest);

        if ($res) {
            $this->dispatch('alert', [
                'text' => 'Purchase Request Updated Successfully',
                'color' => 'teal',
                'title' => 'Success'
            ])->to(\App\Livewire\Pages\Afms\Procurement::class);

            $this->tab = 'Request';
        }

    }

    public function edit(PurchaseRequest $purchaseRequest) {
        $this->purchaseRequest = $purchaseRequest;
        $this->requestForm->fillform($this->purchaseRequest);
    }

    public function viewDetails(Procurement $procurement) {
        return redirect()->route('pmu.show', $procurement->id);
    }

    public function onSubmit(CreateRequest $createRequest) {

        $existingRequest = PurchaseRequest::where('procurement_id', $this->procurement_id)->first();
        if ($existingRequest) {
            $this->dispatch('modal:add-request-close');
            return $this->dispatch('alert', [
                'text' => 'Request Already Added',
                'color' => 'red',
                'title' => 'Failed'
            ])->to(\App\Livewire\Pages\Afms\Procurement::class);
        }
        $this->dispatch('modal:add-request-close');
        $this->dispatch('alert', [
                'text' => 'Request Added Successfully',
                'color' => 'teal',
                'title' => 'Success'
            ])->to(\App\Livewire\Pages\Afms\Procurement::class);
        return $this->requestForm->submit($createRequest, $this->procurement_id);
    }

   public function submitToOrder(PurchaseRequest $purchaseRequest) {

    $existingOrder = PurchaseOrder::where('purchase_request_id' , $purchaseRequest->id)->first();

    if (!$existingOrder) {
    // create here
    PurchaseOrder::create([
        'procurement_id' => $purchaseRequest->procurement->id, // app
        'purchase_request_id' => $purchaseRequest->id, // pr
        'abc_based_app' => $purchaseRequest->procurement->id, // app
        'abc' => $purchaseRequest->procurement->id, // pr
        'date_posted' => $purchaseRequest->id // pr
    ]);

        $this->dispatch('procurement-tab', tab: 'Orders');
        $this->dispatch('alert', [
            'text' => 'Data Successfully Added to Order',
            'color' => 'teal',
            'title' => 'Success'
        ])->to(\App\Livewire\Pages\Afms\Procurement::class);

        return $this->dispatch('procurement-order-refresh')->to(ProcurementOrder::class);

    }

    return $this->dispatch('alert', [
        'text' => 'Data Already Added to Order',
        'color' => 'yellow',
        'title' => 'Failed'
    ])->to(\App\Livewire\Pages\Afms\Procurement::class);
   }

    #[Computed()]
    public function getAnnuals()
    {
        return Procurement::all(['id', 'code'])
            ->map(fn($procurement) => [
                'label' => $procurement->code,
                'value' => $procurement->id,
            ]);
    }


    public function render()
    {
        return view('livewire.pages.afms.components.procurement-request');
    }
}
