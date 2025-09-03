<x-table :headers="$headers" :rows="$this->rows" filter :quantity="[3, 5, 10]" paginate>
    @interact('column_completed', $requisition)
        @if ($requisition->completed)
            <x-badge text="Completed" color="green" outline />
        @else
            <x-badge text="Pending" color="red" outline />
        @endif
    @endinteract
    @interact('column_action', $requisition)
        <x-button.circle color="teal" icon="magnifying-glass" wire:click="view({{ $requisition }})" />
        @if (!$requisition->completed)
            <x-button.circle color="red" loading="deleteRequisition" icon="trash"
                wire:click="deleteRequisition({{ $requisition->id }})" />
        @endif
    @endinteract
</x-table>

