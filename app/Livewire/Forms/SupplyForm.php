<?php

namespace App\Livewire\Forms;

use App\Actions\Supply\CreateSupplyAction;
use App\Actions\Supply\DeleteSupplyAction;
use App\Actions\Supply\EditSupplyAction;
use App\Models\Supply;
use Livewire\Attributes\Rule;
use Livewire\Form;

class SupplyForm extends Form
{
    #[Rule(['required', 'min:6'])]
    public $name;

    #[Rule(['required'])]
    public $category;

    #[Rule(['required'])]
    public $unit;


    public function create(CreateSupplyAction $create_supply_action){
        $this->validate();
        $create_supply_action->handle($this->toArray());
    }

    public function update(Supply $supply, EditSupplyAction $edit_supply_action) {
        $this->validate();
        $edit_supply_action->handle($supply, $this->toArray());
        $this->reset();
    }

    public function fillForm(Supply $supply): void {
        $this->name = $supply->name;
        $this->category = $supply->category;
        $this->unit = $supply->unit;
    }

    public function toArray(): array {
        return [
            'name' => $this->name,
            'category' => $this->category,
            'unit' => $this->unit
        ];
    }
}
