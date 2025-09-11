<?php

namespace App\Livewire\Pages\Afms;

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

    public function mount()
    {
        $this->headers = [
            ['index' => 'name', 'label' => 'Name'],
            ['index' => 'office', 'label' => 'Office Designation'],
            ['index' => 'role', 'label' => 'Roles'],
            ['index' => 'action']
        ];
    }

    #[Computed()]
    public function rows()
    {
        return User::query()
            ->with('roles')
            ->when($this->search, function (Builder $query) {
                $query->where('name', 'like', "%{$this->search}%");
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
