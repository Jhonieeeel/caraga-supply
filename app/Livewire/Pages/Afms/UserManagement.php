<?php

namespace App\Livewire\Pages\Afms;

use App\Actions\Employee\CreateEmployee;
use App\Actions\Guest\CreateGuest;
use App\Actions\User\CreateUser;
use App\Livewire\Forms\EmployeeForm;
use App\Livewire\Forms\GuestForm;
use App\Livewire\Forms\UserForm;
use App\Models\Section;
use App\Models\Unit;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use TallStackUi\Traits\Interactions;

class UserManagement extends Component
{
    use Interactions;

    public $tab = 'Users';

    // forms
    public UserForm $userForm;
    public EmployeeForm $employeeForm;
    public GuestForm $guestForm;
    public $role_id;
    public $unitName;
    public $sectionName;
    public $divisionName;

    // guest form
    public $agency;


    public function createGuest(CreateUser $createUser, CreateGuest $createGuest) {
        $this->authorize('manage-users');

        [$user, $password] = $this->userForm->submitGuest($createUser);
        $this->guestForm->fillform($user->id, $this->agency);
        $this->guestForm->submit($createGuest);

        // Shown once so the operator can hand the temporary password to the guest.
        $this->dialog()
            ->success('Guest Created', "Temporary password for {$user->email}: {$password} (copy it now, it will not be shown again).")
            ->send();
    }

    public function create(CreateUser $create_user, CreateEmployee $create_employee)
    {
        $this->authorize('manage-users');

        $this->validate([
            'role_id' => 'required|exists:roles,id',
            'sectionName' => 'required|string|max:255',
            'unitName' => 'required|string|max:255',
            'divisionName' => [
                function ($attribute, $value, $fail) {
                    $sectionIsAfms = strtolower(trim((string) $this->sectionName)) === 'afms';

                    if (blank($value)) {
                        if (! $sectionIsAfms) {
                            $fail('The division field is required.');
                        }

                        return;
                    }

                    if (mb_strlen($value) > 255) {
                        $fail('The division field must not exceed 255 characters.');
                    }
                },
            ],
        ]);

        // Only a SUPER-ADMIN may grant ADMIN/SUPER-ADMIN.
        $this->authorize('grantRole', [User::class, Role::findOrFail($this->role_id)]);

        $section = Section::firstOrCreate(['name' => trim($this->sectionName)]);
        $unit = Unit::firstOrCreate([
            'name' => trim($this->unitName),
            'section_id' => $section->id,
        ]);

        $createdUser = $this->userForm->submit($create_user, $this->role_id);
        $this->employeeForm->fillForm($unit->id, $section->id, $createdUser->id, $this->divisionName ? trim($this->divisionName) : null);
        $this->employeeForm->submit($create_employee);

        $this->dispatch('modal:add-user-close');

        $this->dispatch('refresh-users');

        return redirect(route('user-management.index'));
    }

    #[Computed()]
    public function roles() {
        return Role::all(['id', 'name'])
            ->map(fn($role) => [
                'label' => $role->name,
                'value' => $role->id,
            ]);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.user-management');
    }
}
