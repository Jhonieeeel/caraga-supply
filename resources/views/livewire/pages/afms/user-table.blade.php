<div class="space-y-6">
    <div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8 bg-white border shadow rounded">
        <div class="flex items-center justify-between sm:pb-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                User Management
            </h2>
            @role('Super Admin')
                <x-button md x-on:click="$modalOpen('add-user')" icon="users" position="right">Add User</x-button>
            @endrole
        </div>
        {{-- content --}}
        <div class="pb-4">
            <x-table :$headers :rows="$this->rows" filter :quantity="[2, 5, 10]" paginate loading>
                @interact('column_user.roles', $row)
                    @foreach ($row->user->roles as $role)
                        <x-badge text="{{ $role->name }}" color="blue" />
                    @endforeach
                @endinteract
                @interact('column_section', $row)
                    {{ $row->section?->name }} - {{ $row->unit?->name }}
                @endinteract

                @interact('column_action', $employee)
                    <x-button.circle md flat icon="eye" wire:click="view({{ $employee->user->id }})" />
                @endinteract
            </x-table>
        </div>

        {{-- add user modal --}}
        <x-modal size="lg" title="Add User Form" id="add-user">
            <form wire:submit.prevent='create'>
                <div class="grid sm:grid-cols-2 sm:gap-3 gap-5">
                    <div class="sm:col-span-2 space-y-3">
                        <x-input wire:model='userForm.name' label="Name *" />
                        <x-input wire:model='userForm.email' label="Email *" />
                        <x-input wire:model='userForm.Dtrnum' label="Dtr Number *" />
                    </div>
                    <x-select.styled label="Section *" wire:model.live="sectionId" :options="$this->sections" searchable />
                    <x-select.styled label="Unit *" wire:model.live="unitId" :options="$this->units" searchable />
                    <x-password wire:model="userForm.password" label="Password *" typing-only />
                    <x-password wire:model="userForm.password_confirmation" label="Confirm Password *" />
                </div>
                <div class="sm:py-4 ">
                    <x-button submit icon="plus-circle" position="right">Submit</x-button>
                </div>
            </form>
        </x-modal>
    </div>
    <livewire:pages.afms.components.user-detail />
</div>

