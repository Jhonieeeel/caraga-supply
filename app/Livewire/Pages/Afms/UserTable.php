<?php

namespace App\Livewire\Pages\Afms;

use App\Actions\Employee\CreateEmployee;
use App\Actions\User\CreateUser;
use App\Livewire\Forms\EmployeeForm;
use App\Livewire\Forms\UserForm;
use App\Models\Employee;
use App\Models\Section;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserTable extends Component
{
    use WithPagination;

    public array $headers = [];

    public ?int $quantity = 5;
    public $search;

    // Form
    public UserForm $userForm;
    public EmployeeForm $employeeForm;

    // dependent select
    public ?int $sectionId = null;
    public ?string $sectionName = null;

    public ?int $unitId = null;
    public ?string $unitName = null;


    // roles
    public $role_id;

    public function mount()
    {
        $this->headers = [
            ['index' => 'user.name', 'label' => 'Name'],
            ['index' => 'section', 'label' => 'Office Designation'],
            ['index' => 'user.roles', 'label' => 'Roles'],
            ['index' => 'action', 'label' => 'Action']
        ];
    }

    public function view(User $user)
    {
        $this->dispatch('user-detail', user: $user);
    }


    #[On('refresh-users')]
    public function updateList($id = null) {}

    #[Computed()]
    public function rows()
    {
        return Employee::query()
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($user) {
                    $user->where('name', 'like', "%{$this->search}%");
                });
            })
            ->paginate($this->quantity)
            ->withQueryString();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.user-table');
    }
}
