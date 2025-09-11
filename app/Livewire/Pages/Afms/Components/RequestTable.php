<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Livewire\Forms\RequisitionForm;
use App\Livewire\Pages\Afms\RequisitionTable;
use App\Models\Requisition;
use Livewire\Component;
use Illuminate\Contracts\Database\Query\Builder;
use Livewire\Attributes\Computed;
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

    public function render()
    {
        return view('livewire.pages.afms.components.request-table');
    }

    public function deleteRequisition($requisitionId)
    {
        Requisition::find($requisitionId)->delete();

        $this->dispatch('update-list');

        return $this->dispatch('alert', [
            'text' => 'Requisition Deleted successfully.',
            'color' => 'teal',
            'title' => 'Deleted'
        ]);
    }

    #[On('update-list')]
    public function updateList($id = null) {}

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
        $currentRequisition = Requisition::find($requisition->id);
        // to Parent
        $this->dispatch('change-tab', tab: 'Detail')->to(RequisitionTable::class);

        // to Detail Tab
        $this->dispatch('view-requisition', requisition: $currentRequisition)->to(RequestDetail::class);

        // to RIS Tab
        $this->dispatch('current-data', requisition: $currentRequisition)->to(RequestRIS::class);
    }
}
