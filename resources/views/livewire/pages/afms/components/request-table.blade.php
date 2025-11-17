<div>
    @php
        $isAdmin = auth()->user()->hasRole('Super Admin');
        $user = auth()->user()->hasRole('User');
        $authUser = auth()->user()->id;
    @endphp
    <span wire:loading.delay wire:target='view'>
        <x-loading />
    </span>
    <x-table :headers="$headers" :rows="$this->rows" filter :quantity="[3, 5, 10]" loading paginate class="sm:pb-6">
        @interact('column_completed', $requisition)
            @if ($requisition->completed)
                <x-badge text="Completed" color="green" />
            @else
                <x-badge text="Pending" color="red" />
            @endif
        @endinteract
        @interact('column_action', $requisition)
            <x-button.circle flat color="teal" icon="magnifying-glass" loading="view"
                wire:click="view({{ $requisition->id }})" />
            @if (auth()->user()->hasRole('Super Admin') ||
                    (!$requisition->completed && auth()->user()->id === $requisition->user_id))
                <x-button.circle flat color="red" loading="deleteRequisition" icon="trash"
                    wire:click="deleteRequisition({{ $requisition->id }})" />
            @endif
        @endinteract
    </x-table>

</div>

