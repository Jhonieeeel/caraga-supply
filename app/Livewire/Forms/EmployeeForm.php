<?php

namespace App\Livewire\Forms;

use App\Actions\Employee\CreateEmployee;
use Livewire\Attributes\Validate;
use Livewire\Form;

class EmployeeForm extends Form
{
    #[Validate('exists:sections,id')]
    public ?int $section_id = null;

    #[Validate('exists:units,id')]
    public ?int $unit_id = null;

    #[Validate('exists:users,id')]
    public ?int $user_id = null;


      public function fillForm($unit_id, $section_id, $user_id): void
    {
        $this->section_id = $section_id;
        $this->unit_id = $unit_id;
        $this->user_id = $user_id;
    }

    public function toArray()
    {
        return [
            'section_id' => $this->section_id,
            'unit_id' => $this->unit_id,
            'user_id' => $this->user_id,
        ];
    }

    public function submit(CreateEmployee $create_employee)
    {
        $this->reset();

        return $create_employee->handle($this->toArray());
    }
}
