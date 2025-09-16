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
use Livewire\Component;
use Livewire\WithPagination;

class UserTable extends Component
{
    use WithPagination;

    public array $headers = [];

    public ?int $quantity = 5;
    public ?string $search = null;

    // Form
    public UserForm $userForm;
    public EmployeeForm $employeeForm;

    // dependent select
    public ?int $sectionId = null;
    public ?string $sectionName = null;

    public ?int $unitId = null;
    public ?string $unitName = null;

    public function mount()
    {
        $this->headers = [
            ['index' => 'user.name', 'label' => 'Name'],
            ['index' => 'section', 'label' => 'Office Designation'],
            ['index' => 'user.roles', 'label' => 'Roles'],
            ['index' => 'action']
        ];
    }

    public function view(User $user)
    {
        $this->dispatch('user-detail', user: $user);
    }

    public function create(CreateUser $create_user, CreateEmployee $create_employee)
    {
        $createdUser = $this->userForm->submit($create_user);
        $this->employeeForm->submit($create_employee, $createdUser);

        return;
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
    public function rows()
    {
        return Employee::query()
            ->with(['section', 'section', 'user'])
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($userQuery) {
                    return $userQuery->where('name', 'like', '%' . $this->search . '%');
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
