<?php

namespace App\Livewire\Forms;

use App\Actions\Supplier\CreateSupplierAction;
use App\Actions\Supplier\EditSupplierAction;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Form;

class SupplierForm extends Form
{
    public $business_name;

    public $tin;

    public $contact_person;

    public $email;

    public $phone;

    public $address;

    public $philgeps_no;

    public $philgeps_membership = 'Red';

    public $notes;

    protected function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'tin' => ['required', 'string', 'max:50', Rule::unique('suppliers', 'tin')->ignore($this->supplier?->id)],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'philgeps_no' => ['nullable', 'string', 'max:100'],
            'philgeps_membership' => ['required', Rule::in(['Red', 'Platinum'])],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public ?Supplier $supplier = null;

    public function create(CreateSupplierAction $action): Supplier
    {
        $this->validate();

        $data = $this->toArray();
        $data['created_by'] = Auth::id();

        $supplier = $action->handle($data);
        $this->reset();

        return $supplier;
    }

    public function update(Supplier $supplier, EditSupplierAction $action): Supplier
    {
        $this->supplier = $supplier;
        $this->validate();

        $data = $this->toArray();
        $data['updated_by'] = Auth::id();

        $result = $action->handle($supplier, $data);
        $this->reset();

        return $result;
    }

    public function fillForm(Supplier $supplier): void
    {
        $this->supplier = $supplier;
        $this->business_name = $supplier->business_name;
        $this->tin = $supplier->tin;
        $this->contact_person = $supplier->contact_person;
        $this->email = $supplier->email;
        $this->phone = $supplier->phone;
        $this->address = $supplier->address;
        $this->philgeps_no = $supplier->philgeps_no;
        $this->philgeps_membership = $supplier->philgeps_membership;
        $this->notes = $supplier->notes;
    }

    public function toArray(): array
    {
        return [
            'business_name' => $this->business_name,
            'tin' => $this->tin,
            'contact_person' => $this->contact_person,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'philgeps_no' => $this->philgeps_no,
            'philgeps_membership' => $this->philgeps_membership,
            'notes' => $this->notes,
        ];
    }
}
