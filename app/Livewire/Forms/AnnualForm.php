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


    protected function rules(): array
    {
        return [
            // Unique Code — ignore current record if updating
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('procurements', 'code')->ignore($this->procurement->id ?? null),
            ],

            // Core fields
            'notice_of_award'       => ['required', 'string', 'max:255'],
            'contract_signing'      => ['required', 'string', 'max:255'],
            'project_title'         => ['required', 'string', 'max:255'],
            'source_of_funds'       => ['required', 'string', 'max:255'],
            'estimated_budget_total'=> ['required', 'numeric', 'min:0'],
            'estimated_budget_co'   => ['required', 'numeric', 'min:0'],
            'estimated_budget_mooe' => ['required', 'numeric', 'min:0'],
            'pmo_end_user'          => ['required', 'string', 'max:255'],
            'early_activity'        => ['required', 'string', 'max:255'],
            'mode_of_procurement'   => ['required', 'string', 'max:255'],
            'advertisement_posting' => ['required', 'string', 'max:255'],
            'submission_bids'       => ['required', 'string', 'max:255'],
            'app_year'              => ['required', 'integer', 'digits:4'],
            'remarks'               => ['required', 'string'],
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
