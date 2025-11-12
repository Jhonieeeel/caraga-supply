<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserRoles extends Component
{
    public $quantity = 5;
    public $search;

    public $currentRoles = [];

    public $selectedRoles = [];
    public $user_id;


    public function mount() {
        $roles = Role::all();
        foreach($roles as $role) {
            $this->currentRoles[] = $role;
        }
    }

    #[Computed()]
    public function userRoles()
    {
        $user = User::find($this->user_id);
        return $user ? $user->roles : collect();
    }

    #[Computed()]
    public function users() {
        return User::all(['id', 'name'])
            ->map(fn($user) => [
                'label' => $user->name,
                'value' => $user->id,
            ]);
    }


    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.components.user-roles');
    }
}
