<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Livewire\Forms\UserForm;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

class UserDetail extends Component
{

    public User $user;
    public UserForm $userForm;

    #[On('user-detail')]
    public function userDetail(User $user)
    {
        $this->user = $user;
        $this->userForm->fillForm($user);
    }

    public function render()
    {
        return view('livewire.pages.afms.components.user-detail');
    }
}
