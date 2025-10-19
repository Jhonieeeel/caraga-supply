<?php

namespace App\Livewire\Forms;

use App\Actions\Procurement\CreateAnnual;
use App\Actions\Procurement\UpdateAnnual;
use App\Models\Procurement;
use Livewire\Form;
use Illuminate\Validation\Rule;
class AnnualForm extends Form
{
    public ?Procurement $procurement = null;

    public string $code = '';
    public string $notice_of_award = '';
    public string $contract_signing = '';
    public string $project_title = '';
    public string $source_of_funds = '';
    public int $estimated_budget_total = 0;
    public int $estimated_budget_co = 0;
    public int $estimated_budget_mooe = 0;
    public string $pmo_end_user = '';
    public string $early_activity = '';
    public string $mode_of_procurement = '';
    public string $advertisement_posting = '';
    public string $submission_bids = '';
    public int $app_year = 0;
    public string $remarks = '';

   protected function rules() {
    return [
        'code' => [
            'required', 'string',
            Rule::unique('procurements', 'code')
                ->ignore($this->procurement->id ?? null), // Use ID and handle null
        ],
        'notice_of_award' => 'required|string',
        'contract_signing' => 'required|string',
        'project_title' => 'required|string',
        'source_of_funds' => 'required|string',
        'estimated_budget_total' => 'required|numeric',
        'estimated_budget_co' => 'required|numeric',
        'estimated_budget_mooe' => 'required|numeric',
        'pmo_end_user' => 'required|string',
        'early_activity' => 'required|string',
        'mode_of_procurement' => 'required|string',
        'advertisement_posting' => 'required|string',
        'submission_bids' => 'required|string',
        'app_year' => 'required|integer',
        'remarks' => 'required|string',
    ];
    }

    public function update(Procurement $procurement, UpdateAnnual $updateAnnual): Procurement
    {
        $this->procurement = $procurement;
        $this->validate();
        $annual = $updateAnnual->handle($procurement, $this->toArray());
        $this->reset();
        return $annual;
    }


    public function fillForm(Procurement $procurement): void
    {
        $this->code = $procurement->code;
        $this->notice_of_award = $procurement->notice_of_award;
        $this->contract_signing = $procurement->contract_signing;
        $this->project_title = $procurement->project_title;
        $this->source_of_funds = $procurement->source_of_funds;
        $this->estimated_budget_total = $procurement->estimated_budget_total;
        $this->estimated_budget_co = $procurement->estimated_budget_co;
        $this->estimated_budget_mooe = $procurement->estimated_budget_mooe;
        $this->pmo_end_user = $procurement->pmo_end_user;
        $this->early_activity = $procurement->early_activity;
        $this->mode_of_procurement = $procurement->mode_of_procurement;
        $this->advertisement_posting = $procurement->advertisement_posting;
        $this->submission_bids = $procurement->submission_bids;
        $this->app_year = $procurement->app_year;
        $this->remarks = $procurement->remarks;
    }

    public function toArray(): array
    {
        return [
            'code'=> $this->code,
            'notice_of_award'=> $this->notice_of_award,
            'contract_signing'=> $this->contract_signing,
            'project_title'=> $this->project_title,
            'source_of_funds'=> $this->source_of_funds,
            'estimated_budget_total'=> $this->estimated_budget_total,
            'estimated_budget_co'=> $this->estimated_budget_co,
            'estimated_budget_mooe'=> $this->estimated_budget_mooe,
            'pmo_end_user'=> $this->pmo_end_user,
            'early_activity'=> $this->early_activity,
            'mode_of_procurement'=> $this->mode_of_procurement,
            'advertisement_posting'=> $this->advertisement_posting,
            'submission_bids'=> $this->submission_bids,
            'app_year'=> $this->app_year,
            'remarks'=> $this->remarks,
        ];
    }

    public function onCreate(CreateAnnual $createAnnual): Procurement
    {
        $this->validate();
        return $createAnnual->handle($this->toArray());
    }
}
