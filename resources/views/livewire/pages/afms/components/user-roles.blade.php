<div class="p-2 space-y-4">
    <h1 class="text-lg font-bold">User</h1>
    <div class="grid grid-cols-3">
        <div class="col-span-2">
            <x-select.styled label="Select User *" wire:model.live="user_id" :options="$this->users" searchable />
        </div>
    </div>
    <h1 class="text-lg font-bold">Roles</h1>
    <div class="sm:flex justify-around">
        @foreach ($this->roles as $role)
            <p>test</p>
            <x-checkbox wire:model.live="roles" value="{{ $role->id }}" label="{{ $role->name }}" />
        @endforeach
    </div>
</div>

