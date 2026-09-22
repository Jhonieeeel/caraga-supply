<?php

namespace App\Livewire\Pages\Afms;

use App\Actions\Employee\CreateEmployee;
use App\Actions\User\CreateUser;
use App\Livewire\Forms\EmployeeForm;
use App\Livewire\Forms\UserForm;
use App\Models\Employee;
use App\Models\Requisition;
use App\Models\Section;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use TallStackUi\Traits\Interactions;

class UserTable extends Component
{
    use WithPagination, Interactions;

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

    public function deleteUser(User $user)
    {
        if ($user->id === Auth::id()) {
            $this->dialog()->error('Error', 'You cannot delete your own account.')->send();
            return;
        }

        $hasRequisitions = Requisition::where('user_id', $user->id)
            ->orWhere('requested_by', $user->id)
            ->orWhere('approved_by', $user->id)
            ->orWhere('issued_by', $user->id)
            ->orWhere('received_by', $user->id)
            ->exists();

        if ($hasRequisitions) {
            $this->dialog()->error('Error', 'Cannot delete this user because they have related requisition records.')->send();
            return;
        }

        $this->dialog()
            ->question('Warning', 'Are you sure you want to delete this user? This action cannot be undone.')
            ->confirm('Confirm', 'confirmed', params: ['message' => 'User Deleted', 'id' => $user->id])
            ->cancel('Cancel', 'cancelled', 'User Deletion Cancelled')
            ->send();
    }

    public function confirmed(array $data)
    {
        $user = User::findOrFail($data['id']);
        $user->delete();

        $this->dialog()->success('Success', $data['message'])->send();
    }

    public function cancelled(string $message): void
    {
        $this->dialog()->error('Cancelled', $message)->send();
    }

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
