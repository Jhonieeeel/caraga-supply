<div class="space-y-6">
    <div class="pb-4">
        <x-table :$headers :rows="$this->rows" :quantity="[2, 5, 10]" paginate loading filter>
            @interact('column_user.roles', $row)
                @foreach ($row->user->roles as $role)
                    @php
                        $color = match ($role->name) {
                            'Super Admin' => 'blue',
                            'Admin' => 'green',
                            default => 'gray',
                        };
                    @endphp
                    <x-badge text="{{ $role->name }}" color="{{ $color }}" />
                @endforeach
            @endinteract
            @interact('column_section', $row)
                {{ $row->section?->name }} - {{ $row->unit?->name }}
            @endinteract
            @role('Super Admin')
                @interact('column_action', $employee)
                    <x-button.circle md flat icon="eye" wire:click="view({{ $employee->user->id }})" />
                @endinteract
            @endrole
        </x-table>
    </div>
</div>

