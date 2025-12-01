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
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserManagement extends Component
{
    public $tab = 'Users';

    // forms
    public UserForm $userForm;
    public EmployeeForm $employeeForm;
    public GuestForm $guestForm;
    public $role_id;
    public $unitId;
    public $sectionId;

    // guest form
    public $agency;


    public function createGuest(CreateUser $createUser, CreateGuest $createGuest) {

        $user = $this->userForm->submitGuest($createUser);
        $this->guestForm->fillform($user->id, $this->agency);
        $this->guestForm->submit($createGuest);

    }

    public function create(CreateUser $create_user, CreateEmployee $create_employee)
    {
        $createdUser = $this->userForm->submit($create_user, $this->role_id);
        $this->employeeForm->fillForm($this->unitId, $this->sectionId, $createdUser->id);
        $this->employeeForm->submit($create_employee);

        $this->dispatch('modal:add-user-close');

        $this->dispatch('refresh-users');

        return redirect(route('user-management.index'));
    }

    #[Computed]
    public function units()
    {
        return Unit::where('section_id', $this->sectionId)
            ->get()
            ->map(fn($unit) => [
                'label' => $unit->name,
                'value' => $unit->id,
            ])
            ->toArray();
    }

    #[Computed()]
    public function sections()
    {
        return Section::all(['id', 'name'])
            ->map(fn($section) => [
                'label' => $section->name,
                'value' => $section->id,
            ]);
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
