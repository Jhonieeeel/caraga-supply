<?php

namespace App\Livewire\Forms;

use App\Actions\Procurement\CreateRequest;
use App\Actions\Procurement\UpdateRequest;
use App\Models\PurchaseRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Form;

class RequestForm extends Form
{

    #[Validate(['nullable', 'date'])]
    public $closing_date;

    #[Validate(rule: ['nullable', 'date'])]
    public $input_date;

    #[Validate(['nullable', 'file', 'mimes:pdf'])]
    public $app_spp_pdf_file;

    #[Validate(['nullable', 'string', 'max:255'])]
    public $app_spp_pdf_filename;

    #[Validate(['nullable', 'file', 'mimes:pdf'])]
    public $philgeps_pdf_file;

    #[Validate(['nullable', 'string', 'max:255'])]
    public $philgeps_pdf_filename;

    #[Validate(['nullable', 'string', 'max:100'])]
    public $pr_number;

    #[Validate(['nullable', 'numeric', 'min:0'])]
    public $abc;

    #[Validate(['nullable', 'email'])]
    public $email_posting;

    #[Validate(['nullable', 'date'])]
    public $date_posted;

    // fks
    #[Validate(['required', 'exists:procurements,id'])]
    public $procurement_id;

    #[Validate(['nullable', 'exists:procurements,id'])]
    public $app_year;

    #[Validate(['nullable', 'exists:procurements,id'])]
    public $abc_based_app;


    // for storing
    public $new_app_spp_pdf_file;
    public $new_philgeps_pdf_file;

    // file update
    public $currentAppFile;
    public $currentPhilGepsFile;

    public function update(UpdateRequest $updateRequest, PurchaseRequest $purchaseRequest)
    {
        $this->validate();

        if ($this->app_spp_pdf_file) {
            Storage::disk('public')->delete($this->currentAppFile);
            $this->new_app_spp_pdf_file = $this->app_spp_pdf_file->store('pr-records', 'public');
        } else {
            $this->new_app_spp_pdf_file = $this->currentAppFile;
        }

        if ($this->philgeps_pdf_file) {
            Storage::disk('public')->delete($this->currentPhilGepsFile);
            $this->new_philgeps_pdf_file = $this->philgeps_pdf_file->store('pr-records', 'public');
        } else {
            $this->new_philgeps_pdf_file = $this->currentPhilGepsFile;
        }

        return $updateRequest->handle($purchaseRequest, $this->toArray());
    }

    public function submit(CreateRequest $createRequest, $procurement_id)
    {
        $this->procurement_id = $procurement_id;
        $this->abc_based_app = $procurement_id;
        $this->app_year = $procurement_id;

        $this->validate();

        if ($this->app_spp_pdf_file) {
            $this->new_app_spp_pdf_file = $this->app_spp_pdf_file->store('pr-records', 'public');
        }

        if ($this->philgeps_pdf_file) {
            $this->new_philgeps_pdf_file = $this->philgeps_pdf_file->store('pr-records', 'public');
        }

        $request = $createRequest->handle($this->toArray());

        return $request;
    }


    public function toArray(): array
    {

        return [
            'closing_date' => $this->closing_date,
            'input_date' => $this->input_date,
            'app_spp_pdf_file' => $this->new_app_spp_pdf_file,
            'app_spp_pdf_filename' => $this->app_spp_pdf_filename,
            'philgeps_pdf_file' => $this->new_philgeps_pdf_file,
            'philgeps_pdf_filename' => $this->philgeps_pdf_filename,
            'pr_number' => $this->pr_number,
            'abc' => $this->abc,
            'email_posting' => $this->email_posting,
            'date_posted' => $this->date_posted,
            'procurement_id' => $this->procurement_id,
            'app_year' => $this->app_year,
            'abc_based_app' => $this->abc_based_app,
        ];
    }

    public function fillform(PurchaseRequest $request): void
    {
        // if update CURRENT
        $this->currentAppFile = $request->app_spp_pdf_file;
        $this->currentPhilGepsFile = $request->philgeps_pdf_file;

        // file
        $this->app_spp_pdf_file = null;
        $this->philgeps_pdf_file = null;

        // date
        $this->closing_date = $request->closing_date;
        $this->input_date = $request->input_date;
        $this->date_posted = $request->date_posted;

        // text
        $this->app_spp_pdf_filename = $request->app_spp_pdf_filename;
        $this->philgeps_pdf_filename = $request->app_spp_pdf_filename;
        $this->pr_number = $request->pr_number;
        $this->abc = $request->abc;
        $this->email_posting = $request->email_posting;

        // fks
        $this->procurement_id = $request->procurement_id;
        $this->app_year = $request->app_year;
        $this->abc_based_app = $request->app_based_app;
    }

}
