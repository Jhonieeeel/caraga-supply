<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserRoles extends Component
{
    public $quantity = 5;
    public $search;


    public $selectedUser;

    public $currentRoles = [];
    public $selectedRoles = [];
    public $user_id;
    public $count;

    public function mount() {
        $roles = Role::all();
        foreach($roles as $role) {
            $this->currentRoles[] = $role;
        }
    }


    public function updatedSelectedUser($value)
    {
        if (!$value) {
            $this->selectedRoles = [];
            return;
        }

        $user = User::find($value);

        // Load the user’s current roles into the checkboxes
        $this->selectedRoles = $user->roles->pluck('id')->toArray();
        Log::info('Selected Roles: ' . implode(',', $this->selectedRoles));
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
