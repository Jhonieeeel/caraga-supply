<?php

namespace App\Livewire\Forms;

use App\Actions\User\CreateUser;
use App\Models\User;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Spatie\Permission\Models\Role;

class UserForm extends Form
{
    #[Validate('required|string|max:255')]
    public ?string $name = null;

    #[Validate('required|string|max:255|email|unique:users,email')]
    public ?string $email = null;

    #[Validate('required|string|min:8|confirmed')]
    public ?string $password = null;

    #[Validate('nullable')]
    public $dtr_number;

    #[Validate('nullable')]
    public $designation;

    #[Validate('nullable')]
    public $office_position;

    #[Validate('required|in:male,female')]
    public $gender;

    #[Validate('required|string|min:8')]
    public ?string $password_confirmation = null;

    public function updatePass(User $user) {

        $this->validate();

        $user->update([
            'password' => $this->password
        ]);
    }

    public function updateInfo(User $user) {
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);
    }

    public function submitGuest(CreateUser $createUser) {

       $this->password = 'password';

       $this->password_confirmation = $this->password;

       $this->validate();

       $user = $createUser->handle([
            'name' => $this->name,
            'password' => $this->password,
            'email' => $this->email
       ]);

       $guest = Role::find(3);

       $user->assignRole($guest);

       $this->reset();

        return $user;
    }

    public function submit(CreateUser $create_action, $rold_id)
    {
        $this->validate();

        $user = $create_action->handle($this->toArray());

        $role = Role::find($rold_id);

        $user->assignRole($role);

        $this->reset();

        return $user;
    }

    public function fillForm(User $user): void
    {
        $this->name = $user->name;
        $this->password = $user->password;
        $this->email = $user->email;
        $this->dtr_number = $user->dtr->number ?? '';
        $this->designation = $user->designation ?? '';
        $this->office_position = $user->office_position ?? '';
        $this->gender = $user->gender;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'password' => $this->password,
            'email' => $this->email,
            'dtr_number' => $this->dtr_number,
            'designation' => $this->designation,
            'office_position' => $this->office_position,
            'gender' => $this->gender,
        ];
    }
}
