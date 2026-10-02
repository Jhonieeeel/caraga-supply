<?php

namespace App\Livewire\Forms;

use App\Actions\User\CreateUser;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Spatie\Permission\Models\Role;

class UserForm extends Form
{
    #[Validate('required|string|max:255')]
    public ?string $name = null;

    #[Validate('required|string|max:255|email|unique:users,email')]
    public ?string $email = null;

    #[Validate('nullable')]
    public $dtr_number;

    #[Validate('nullable')]
    public $designation;

    #[Validate('nullable')]
    public $office_position;

    #[Validate('required|in:male,female,prefer_not')]
    public $gender;


    // update

    // the OPERATOR's own password, re-entered to confirm the change
    public ?string $current_password = '';

    #[Validate('required|string|min:8|confirmed')]
    public ?string $password = null;

    public ?string $new_password_confirmation = null;

    // create

    #[Validate('required|string|min:8|confirmed')]
    public ?string $new_password = null;

    public ?string $password_confirmation = null;


    public function updatePass(User $user) {

        $validated = $this->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'new_password' => ['required', 'string', Password::defaults(), 'confirmed'], // looks for new_password_confirmation
        ], [
            'current_password.current_password' => 'Your own password is incorrect.',
        ], [
            'current_password' => 'your password',
            'new_password' => 'new password',
        ]);

        $user->update([
            'password' => $validated['new_password']
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        return $user;
    }

    public function updateInfo(User $user) {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'max:255', 'email', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);
    }

    /**
     * Creates a guest account with a random password.
     *
     * @return array{0: User, 1: string} the user and the generated plain-text password
     */
    public function submitGuest(CreateUser $createUser) {

       $this->validate([
           'name' => 'required|string|max:255',
           'email' => 'required|string|max:255|email|unique:users,email',
       ]);

       $password = Str::password(12);

       $user = $createUser->handle([
            'name' => $this->name,
            'password' => $password,
            'email' => $this->email
       ]);

       $guest = Role::firstOrCreate(['name' => 'Guest', 'guard_name' => 'web']);

       $user->assignRole($guest);

       $this->reset();

        return [$user, $password];
    }

    public function submit(CreateUser $create_action, $rold_id)
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|email|unique:users,email',
            'dtr_number' => 'nullable',
            'designation' => 'nullable',
            'office_position' => 'nullable',
            'gender' => 'required|in:male,female,prefer_not',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $create_action->handle($this->payload());

        $role = Role::findOrFail($rold_id);

        $user->assignRole($role);

        $this->reset();

        return $user;
    }

    public function fillForm(User $user): void
    {
        $this->name = $user->name;
        $this->email = $user->email;
        $this->dtr_number = $user->dtr_number ?? '';
        $this->designation = $user->designation ?? '';
        $this->office_position = $user->office_position ?? '';
        $this->gender = $user->gender;
    }

    public function payload(): array
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
