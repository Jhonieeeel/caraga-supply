<?php

namespace App\Livewire\Forms;

use App\Actions\User\CreateUser;
use App\Models\User;
use Livewire\Attributes\Validate;
use Livewire\Form;

class UserForm extends Form
{
    #[Validate('required|string|max:255')]
    public ?string $name = null;

    #[Validate('required|string|max:255|email|unique:users,email')]
    public ?string $email = null;

    #[Validate('required|string|min:8|confirmed')]
    public ?string $password = null;

    #[Validate('required|string|min:8')]
    public ?string $password_confirmation = null;

    public function submit(CreateUser $create_action)
    {
        $this->validate();

        $user = $create_action->handle($this->toArray());

        // by default
        $user->assignRole('User');

        $this->reset();

        return $user;
    }

    public function fillForm(User $user): void
    {
        $this->name = $user->name;
        $this->password = $user->password;
        $this->email = $user->email;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'password' => $this->password,
            'email' => $this->email,
        ];
    }
}
