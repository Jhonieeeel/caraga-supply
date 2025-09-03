<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Livewire\Forms\RequisitionForm;
use App\Models\Requisition;
use Livewire\Component;
use Illuminate\Contracts\Database\Query\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class RequestTable extends Component
{
    use WithPagination;

    public array $headers = [];
    public string $search = '';
    public int $quantity = 5;

    public Requisition $requisition;
    public RequisitionForm $requestForm;

    public function mount()
    {
        $this->headers = [
            ['index' => 'user.name', 'label' => 'Requested By'],
            ['index' => 'items_count', 'label' => 'Total Requested Items'],
            ['index' => 'completed', 'label' => 'Status'],
            ['index' => 'action']
        ];
    }

    #[On('refresh')]
    public function render()
    {
        return view('livewire.pages.afms.components.request-table');
    }

    public function deleteRequisition($requisitionId)
    {
        Requisition::find($requisitionId)->delete();

        $this->dispatch('refresh')->self();

        return $this->dispatch('alert', [
            'text' => 'Requisition Deleted successfully.',
            'color' => 'teal',
            'title' => 'Deleted'
        ]);
    }

    #[Computed()]
    public function rows()
    {
        return Requisition::query()
            ->with(['user', 'items'])
            ->withCount('items')
            ->when($this->search, function (Builder $query) {
                $query->whereHas('user', function ($user) {
                    $user->where('name', 'like', "%{$this->search}%");
                });
            })
            ->latest()
            ->paginate($this->quantity)
            ->withQueryString();
    }

    public function view(Requisition $requisition)
    {
        $this->requisition = $requisition;

        // to Parent
        $this->dispatch('change-tab', tab: 'Detail');

        // to Detail Tab
        $this->dispatch('view-requisition', id: $requisition->id)->to(RequestDetail::class);

        // to RIS Tab
        $this->dispatch('current-data', id: $requisition->id);
    }
}
