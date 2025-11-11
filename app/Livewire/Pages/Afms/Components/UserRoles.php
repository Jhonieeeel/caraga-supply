<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

class UserRoles extends Component
{
    public $quantity = 5;
    public $search;
    public $roles = [];
    public $user_id;


    #[Computed()]
    public function roles()
    {
        $user = User::find($this->user_id);
        dd($user);
        return $user ?? collect();
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
