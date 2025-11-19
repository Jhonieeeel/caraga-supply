<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Livewire\Forms\RequisitionForm;
use App\Livewire\Pages\Afms\RequisitionTable;
use App\Models\Requisition;
use Livewire\Component;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\WithPagination;

class RequestTable extends Component
{

    use WithPagination;

    public array $headers = [];
    public string $search = '';
    public int $quantity = 5;

    public ?Requisition $requisition = null;
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
        $request = Requisition::find( $requisitionId );

        $deletion = $request->delete();

        if ($deletion) {
            $this->dispatch('alert', [
                'text' => 'Requisition Deleted Successfully.',
                'color' => 'teal',
                'title' => 'Success'
            ]);

            return $this->dispatch('update-request-table');
        }

        return $this->dispatch('update-list');
    }

    #[On('update-request-table')]
    public function updateList() {
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

        $currentRequisition = Requisition::find($requisition->id);

        if (!$currentRequisition) {
            return $this->dispatch('change-tab', 'List')->to(RequisitionTable::class);
        }

        $this->dispatch('change-tab', tab: 'Detail')->to(RequisitionTable::class);

        $this->dispatch('view-requisition', requisition: $currentRequisition->id)
            ->to(RequestDetail::class);

        $this->dispatch('current-data', requisition: $currentRequisition->id)
            ->to(RequestRIS::class);

        return;
    }
}
