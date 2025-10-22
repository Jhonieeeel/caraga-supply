<?php

namespace App\Livewire\Forms;

use App\Actions\Procurement\CreateRequest;
use App\Actions\Procurement\UpdateRequest;
use App\Models\PurchaseRequest;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class RequestForm extends Form
{
    public ?Carbon $closing_date = null;
    public ?Carbon $input_date = null;
    public ?string $app_spp_pdf_file = "";
    public string $app_spp_pdf_filename = "";
    public ?string $philgeps_pdf_file = "";
    public ?string $philgeps_pdf_filename = "";
    public ?string $pr_number = "";
    public ?float $abc = 0;
    public ?string $email_posting = "";
    public ?Carbon $date_posted = null;

    // fks
    public ?int $procurement_id = null;
    public ?int $app_year = null;
    public ?int $abc_based_app = null;



    protected function rules(): array  {
       return [
            'closing_date' => [
                'nullable',
                Rule::date()->format('Y-m-d'),
            ],
            'input_date' => [
                'nullable',
                Rule::date()->format('Y-m-d'),
            ],
            'app_spp_pdf_file' => 'nullable',
            'app_spp_pdf_filename' => 'nullable',
            'philgeps_pdf_file' => 'nullable',
            'philgeps_pdf_filename' => 'nullable',
            'pr_number' => 'nullable',
            'abc' => 'nullable',
            'email_posting' => 'nullable',
            'date_posted' => [
                'nullable',
                Rule::date()->format('Y-m-d'),
            ],
            'procurement_id' => 'required',
            'app_year' => 'nullable',
            'abc_based_app' => 'nullable'
       ];
    }

    public function update(UpdateRequest $updateRequest, PurchaseRequest $purchaseRequest) {
        $this->validate();
        return $updateRequest->handle($purchaseRequest, $this->toArray());
    }

    public function submit(CreateRequest $createRequest) {
        $this->validate();
        $request = $createRequest->handle($this->toArray());
        return $request;
    }

    public function toArray(): array {
        return [
            'closing_date' => $this->closing_date,
            'input_date' => $this->input_date,
            'app_spp_pdf_file' => $this->app_spp_pdf_file,
            'app_spp_pdf_filename' => $this->app_spp_pdf_filename,
            'philgeps_pdf_file' => $this->philgeps_pdf_file,
            'philgeps_pdf_filename' => $this->philgeps_pdf_filename,
            'pr_number' => $this->pr_number,
            'abc' => $this->abc,
            'email_posting' => $this->email_posting,
            'date_posted' => $this->date_posted,
            'procurement_id' => $this->procurement_id,
            'app_year' => $this->app_year,
            'abc_based_app' => $this->abc_based_app
        ];
    }

    public function fillform(PurchaseRequest $request): void {
        $this->closing_date = $request->closing_date;
        $this->input_date = $request->input_date;
        $this->app_spp_pdf_file = $request->app_spp_pdf_file;
        $this->app_spp_pdf_filename = $request->app_spp_pdf_filename;
        $this->philgeps_pdf_file = $request->philgeps_pdf_file;
        $this->philgeps_pdf_filename = $request->philgeps_pdf_filename;
        $this->pr_number = $request->pr_number;
        $this->abc = $request->abc;
        $this->email_posting = $request->email_posting;
        $this->date_posted = $request->date_posted;
        $this->procurement_id = $request->procurement_id;
        $this->app_year = $request->app_year;
        $this->abc_based_app = $request->app_based_app;
    }
}
