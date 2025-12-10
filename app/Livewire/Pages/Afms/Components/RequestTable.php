<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Events\RequisitionUploaded;
use App\Livewire\Forms\RequisitionForm;
use App\Livewire\Pages\Afms\RequisitionTable;
use App\Models\Requisition;
use Livewire\Component;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;

class RequestTable extends Component
{

    use WithPagination, Interactions;

    public array $headers = [];
    public string $search = '';
    public int $quantity = 5;

    public int $loggedInId;

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

        $this->loggedInId = Auth::id();

    }

    public function getListeners()
    {
        return [
            'echo-private:requisitions.' . $this->loggedInId . ',RequestCreated' => 'newRequestNotification',
        ];
    }

    public function newRequestNotification($event)
    {
        Log::info('New Requisition Created: ' . json_encode($event));
    }

    public function render()
    {
        return view('livewire.pages.afms.components.request-table');
    }

    public function deleteRequisition($requisitionId)
    {

        if ($requisitionId) {
           $this->dialog()
                ->question('Warning', 'Are you sure?')
                ->confirm('Confirm', 'confirmed', params: ['message' => 'Request Deleted', 'id' => $requisitionId])
                ->cancel('Cancel', 'cancelled', 'Request Deletion Cancelled')
                ->send();

        }
    }

    public function confirmed(array $data) {
        $this->dialog()->success('Success', $data['message'])->send();

        $requisition = Requisition::findOrFail($data['id'])->delete();

        $this->dispatch('update-request-table');

        return $requisition;
    }

    public function cancelled(string $message): void
    {
        $this->dialog()->error('Cancelled', $message)->send();
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
    }
}
