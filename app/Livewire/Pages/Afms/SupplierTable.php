<?php

namespace App\Livewire\Pages\Afms;

use App\Actions\Supplier\CreateSupplierAction;
use App\Actions\Supplier\DeleteSupplierAction;
use App\Actions\Supplier\EditSupplierAction;
use App\Livewire\Forms\SupplierForm;
use App\Models\Supplier;
use Illuminate\Contracts\Database\Query\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierTable extends Component
{
    use WithPagination;

    public ?string $search = null;
    public ?int $quantity = 5;
    public $headers = [];

    public SupplierForm $supplierForm;
    public ?Supplier $supplier = null;

    public function mount()
    {
        $this->authorize('manage-procurement');

        $this->headers = [
            ['index' => 'business_name', 'label' => 'Business Name'],
            ['index' => 'tin', 'label' => 'TIN'],
            ['index' => 'philgeps_membership', 'label' => 'PhilGEPS Membership'],
            ['index' => 'is_eligible', 'label' => 'Eligible'],
            ['index' => 'action'],
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    #[Computed()]
    public function rows()
    {
        return Supplier::query()
            ->when($this->search, function (Builder $query) {
                return $query->where('business_name', 'like', "%{$this->search}%")
                    ->orWhere('tin', 'like', "%{$this->search}%");
            })
            ->paginate($this->quantity)
            ->withQueryString();
    }

    public function create(CreateSupplierAction $action)
    {
        $this->authorize('manage-procurement');

        $this->supplierForm->create($action);

        $this->dispatch('modal:add-close');
    }

    public function edit(Supplier $supplier)
    {
        $this->authorize('manage-procurement');

        $this->supplier = $supplier;
        $this->supplierForm->fillForm($supplier);
        $this->dispatch('modal:edit-open');
    }

    public function update(EditSupplierAction $action)
    {
        $this->authorize('manage-procurement');

        $this->supplierForm->update($this->supplier, $action);

        $this->dispatch('modal:edit-close');
    }

    public function toggleEligibility(Supplier $supplier)
    {
        $this->authorize('manage-procurement');

        $supplier->update([
            'is_eligible' => !$supplier->is_eligible,
            'eligibility_checked_at' => now(),
            'updated_by' => auth()->id(),
        ]);
    }

    public function delete($id, DeleteSupplierAction $action)
    {
        $this->authorize('manage-procurement');

        $supplier = Supplier::findOrFail($id);

        if ($supplier->purchaseOrders()->exists()) {
            return session()->flash('message', [
                'text' => 'Cannot delete this supplier because it has related purchase orders.',
                'color' => 'red',
                'title' => 'Error',
            ]);
        }

        $action->handle($supplier);

        session()->flash('message', [
            'text' => 'Supplier deleted successfully.',
            'color' => 'green',
            'title' => 'Success',
        ]);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.afms.supplier-table');
    }
}
