<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Actions\Procurement\CreateAnnual;
use App\Actions\Procurement\UpdateAnnual;
use App\Livewire\Forms\AnnualForm;
use App\Livewire\Pages\Afms\Procurement as AfmsProcurement;
use App\Models\Procurement;
use App\Models\PurchaseRequest;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ProcurementAnnual extends Component
{
    public AnnualForm $annualForm;

    public Procurement $procurement;

    public $tab = 'Annual';

    public ?string $search = '';
    public int $quantity = 5;

    public array $years = [];

    public array $headers = [];

    public function onSubmit(CreateAnnual $createAnnual, PurchaseRequest $purchaseRequest) {

        $existingRequest = PurchaseRequest::where('procurement_id', $purchaseRequest->procurement_id)->first();

        if (!$existingRequest) {
            $this->annualForm->onCreate($createAnnual);

            $this->tab = 'Request';

            $this->dispatch('alert', [
                'text' => 'Data Successfully Added to Request',
                'color' => 'teal',
                'title' => 'Success'
            ])->to(AfmsProcurement::class);

            return redirect()->route('pmu.index');
        }

        return $this->dispatch('alert', [
            'text' => 'Data already added to Purchase Request',
            'color' => 'yellow',
            'title' => 'Failed'
        ])->to(AfmsProcurement::class);
    }

    public function mount()
    {
        $startYear = 2020;
        $currentYear = now()->year;

        $this->years = range($startYear, $currentYear);

        $this->headers = [
            ['index' => 'code', 'label' => 'Code PAP'],
            ['index' => 'project_title', 'label' => 'Project Title'],
            ['index' => 'pmo_end_user', 'label' => 'PMO End User'],
            ['index' => 'early_activity', 'label' => 'Is this Early Activity?'],
            ['index' => 'mode_of_procurement', 'label' => 'Mode of Procurement'],
            ['index' => 'advertisement_posting', 'label' => 'Advertisement Posting'],
            ['index' => 'submission_bids', 'label' => 'Submission Bids'],
            ['index' => 'notice_of_award', 'label' => 'Notice Of Awards'],
            ['index' => 'contract_signing', 'label' => 'Contract Signing'],
            ['index' => 'source_of_funds', 'label' => 'Source of Funds'],
            ['index' => 'estimated_budget_total', 'label' => 'Estimated Budget Total'],
            ['index' => 'estimated_budget_mooe', 'label' => 'Estimated Budget MOOE'],
            ['index' => 'estimated_budget_co', 'label' => 'Estimated Budget CO'],
            ['index' => 'remarks', 'label' => 'Remarks'],
            ['index' => 'action', 'label' => 'Action']
        ];
    }

    public function submitToRequest(Procurement $procurement) {
        $existingRequest = PurchaseRequest::where('procurement_id', $procurement->id)->first();
        if (!$existingRequest) {
            $procurement->purchaseRequest()->create([
                'procurement_id' => $procurement->id,
                'abc_based_app' => $procurement->id,
                'app_year' => $procurement->id,
            ]);
            $this->tab = "Request";
            return redirect()->route('pmu.index');
        }
        return $this->dispatch('alert', [
            'text' => 'Data already added to Purchase Request',
            'color' => 'yellow',
            'title' => 'Failed'
        ])->to(AfmsProcurement::class);

    }

    public function onUpdate(UpdateAnnual $updateAnnual, Procurement $procurement)
    {
        $this->annualForm->update($this->procurement, $updateAnnual);
        $this->dispatch('modal:edit-entry-close');
    }

    public function edit(Procurement $procurement)
    {
        $this->procurement = $procurement;
        $this->annualForm->fillForm($procurement);
        $this->dispatch('modal:edit-entry-open');
        $this->dispatch('alert', [
            'text' => 'Annual Procurement Updated Successfully.',
            'color' => 'teal',
            'title' => 'Success'
        ])->to(AfmsProcurement::class);
    }

    #[Computed()]
    public function rows() {
       return Procurement::query()
            ->when($this->search, function ($query) {
                $query->where('code', 'like', '%' . $this->search . '%')
                    ->orWhere('project_title', 'like', '%' . $this->search . '%');
            })
            ->paginate($this->quantity)
            ->withQueryString();
    }

    public function render()
    {
        return view('livewire.pages.afms.components.procurement-annual');
    }
}
