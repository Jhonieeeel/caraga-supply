<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Livewire\Forms\UserForm;
use App\Models\Section;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use TallStackUi\Traits\Interactions;

class UserDetail extends Component
{
    use Interactions;

    public User $user;
    public UserForm $userForm;
    public $role_id;

    // assignment
    public $sectionName;
    public $unitName;
    public $divisionName;

    // userInfo
    public function updatePassword(User $user) {
        $this->authorize('update', User::class);

        $this->userForm->updatePass($this->user);

        return;
    }

    public function updateUserInfo() {
        $this->authorize('update', User::class);

        return $this->userForm->updateInfo($this->user);
    }

    public function updateRole()
    {
        if ($this->user->id === Auth::id()) {
            $this->dialog()->error('Error', 'You cannot change your own role.')->send();
            return;
        }

        $role = Role::findOrFail($this->role_id);

        $this->authorize('changeRole', [$this->user, $role]);

        $this->user->syncRoles([$role]);

        $this->dialog()->success('Success', 'Role updated successfully.')->send();
    }

    public function updateAssignment()
    {
        $this->authorize('update', User::class);

        $this->validate([
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

        $section = Section::firstOrCreate(['name' => trim($this->sectionName)]);
        $unit = Unit::firstOrCreate([
            'name' => trim($this->unitName),
            'section_id' => $section->id,
        ]);

        $this->user->employee->update([
            'section_id' => $section->id,
            'unit_id' => $unit->id,
            'division' => $this->divisionName ? trim($this->divisionName) : null,
        ]);

        $this->dialog()->success('Success', 'Assignment updated successfully.')->send();
    }

    #[Computed()]
    public function roles()
    {
        return Role::all(['id', 'name'])
            ->map(fn($role) => [
                'label' => $role->name,
                'value' => $role->id,
            ]);
    }

    #[On('user-detail')]
    public function userDetail(User $user)
    {
        $this->user = $user;
        $this->userForm->fillForm($this->user);
        $this->role_id = $user->roles->first()?->id;

        $employee = $user->employee;
        $this->sectionName = $employee?->section?->name;
        $this->unitName = $employee?->unit?->name;
        $this->divisionName = $employee?->division;
    }

    public function render()
    {
        return view('livewire.pages.afms.components.user-detail');
    }
}
