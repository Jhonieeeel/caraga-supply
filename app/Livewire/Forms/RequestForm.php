<?php

namespace App\Livewire\Forms;

use App\Actions\Procurement\CreateRequest;
use App\Actions\Procurement\UpdateRequest;
use App\Models\PurchaseRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;
use Livewire\WithFileUploads;

class RequestForm extends Form
{
    use WithFileUploads;
    public ?Carbon $closing_date = null;
    public ?Carbon $input_date = null;
    public $app_spp_pdf_file;
    public ?string $app_spp_pdf_filename = "";
    public $philgeps_pdf_file;
    public ?string $philgeps_pdf_filename = "";
    public ?string $pr_number = "";
    public ?float $abc = 0;
    public ?string $email_posting = "";
    public ?Carbon $date_posted = null;

    // fks
    public ?int $procurement_id = null;
    public ?int $app_year = null;
    public ?int $abc_based_app = null;


    // files
    public ?string $currentAppFile = null;
    public ?string $currentPhilGepsFile = null;

    protected function rules(): array
    {
        return [
            // Foreign key
            'procurement_id'       => ['required', 'exists:procurements,id'],

            // Dates
            'closing_date'         => ['nullable', 'date'],
            'input_date'           => ['nullable', 'date'],
            'date_posted'          => ['nullable', 'date'],

            // Files and filenames
            'app_spp_pdf_file'     => ['nullable', 'file', 'mimes:pdf'],
            'app_spp_pdf_filename' => ['nullable', 'string', 'max:255'],
            'philgeps_pdf_file'    => ['nullable', 'file', 'mimes:pdf'],
            'philgeps_pdf_filename'=> ['nullable', 'string', 'max:255'],

            // Basic details
            'pr_number'            => ['nullable', 'string', 'max:100'],
            'abc'                  => ['nullable', 'numeric', 'min:0'],
            'abc_based_app'        => ['nullable', 'exists:procurements,id'],
            'app_year'             => ['nullable', 'exists:procurements,id'],

            // Other fields
            'email_posting'        => ['nullable', 'email'],
        ];
    }



    public function update(UpdateRequest $updateRequest, PurchaseRequest $purchaseRequest) {
        $this->validate();
        return $updateRequest->handle($purchaseRequest, $this->toArray());
    }

    public function submit(CreateRequest $createRequest, $procurement_id) {
        $this->procurement_id = $procurement_id;
        $this->abc_based_app = $procurement_id;
        $this->app_year = $procurement_id;

        $this->validate();

        Log::info('data',);

        $appPath = Storage::put($this->app_spp_pdf_file, file_get_contents($this->app_spp_pdf_file));
        $philPath = Storage::put($this->philgeps_pdf_file, file_get_contents($this->philgeps_pdf_file));


        Log::info('data',$appPath);

        $request = $createRequest->handle($this->toArray());

        return $request;
    }

    public function toArray(): array
    {
        $appFile = $this->app_spp_pdf_file ?: $this->currentAppFile;
        $philFile = $this->philgeps_pdf_file ?: $this->currentPhilGepsFile;

        return [
            'closing_date' => $this->closing_date,
            'input_date' => $this->input_date,
            'app_spp_pdf_file' => $appFile,
            'app_spp_pdf_filename' => $this->app_spp_pdf_filename,
            'philgeps_pdf_file' => $philFile,
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
        $this->closing_date = $request->closing_date;
        $this->input_date = $request->input_date;

        // uploads start empty
        $this->app_spp_pdf_file = null;
        $this->philgeps_pdf_file = null;

        // store existing file paths separately
        $this->currentAppFile = $request->app_spp_pdf_file;
        $this->app_spp_pdf_filename = $request->app_spp_pdf_filename;

        $this->currentPhilGepsFile = $request->philgeps_pdf_file;
        $this->philgeps_pdf_filename = $request->philgeps_pdf_filename;

        $this->pr_number = $request->pr_number;
        $this->abc = $request->abc;
        $this->email_posting = $request->email_posting;
        $this->date_posted = $request->date_posted;
        $this->procurement_id = $request->procurement_id;
        $this->app_year = $request->app_year;
        $this->abc_based_app = $request->abc_based_app;
    }

}
