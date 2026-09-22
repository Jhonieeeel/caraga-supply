<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Livewire\Forms\UserForm;
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

    // userInfo
    public function updatePassword(User $user) {
        $this->userForm->updatePass($this->user);

        return;
    }

    public function updateUserInfo() {
        return $this->userForm->updateInfo($this->user);
    }

    public function updateRole()
    {
        if ($this->user->id === Auth::id()) {
            $this->dialog()->error('Error', 'You cannot change your own role.')->send();
            return;
        }

        $role = Role::findOrFail($this->role_id);
        $this->user->syncRoles([$role]);

        $this->dialog()->success('Success', 'Role updated successfully.')->send();
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
    }

    public function render()
    {
        return view('livewire.pages.afms.components.user-detail');
    }
}
