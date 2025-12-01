<?php

namespace App\Livewire\Forms;

use App\Actions\Guest\CreateGuest;
use App\Models\Guest;
use Livewire\Attributes\Validate;
use Livewire\Form;

class GuestForm extends Form
{
    #[Validate(['exists:users,id'])]
    public $user_id;

    #[Validate('required')]
    public $agency;


    public function submit(CreateGuest $createGuest) {

        $this->validate();

        $createGuest->handle($this->toArray());

    }

    public function fillform($user_id, $agency) {
        $this->user_id = $user_id;
        $this->agency = $agency;
    }

    public function toArray() {
       return [
            'user_id' => $this->user_id,
            'agency' => $this->agency
        ];
    }
}
