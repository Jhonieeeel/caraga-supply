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

        <x-tab wire:model="tab">
            <x-tab.items tab="Users">
                <livewire:pages.afms.user-table />
            </x-tab.items>
            <x-tab.items tab="Roles">
                <livewire:pages.afms.components.user-roles />
            </x-tab.items>
        </x-tab>

        <div class="my-6">
            <livewire:pages.afms.components.user-detail />
        </div>

        <x-modal size="xl" title="Add User Form" id="add-user">
            <x-tab selected="Employee">
                <x-tab.items tab="Employee">
                    <x-slot:right>
                        <x-icon name="user" class="w-5 h-5" />
                    </x-slot:right>
                    <form wire:submit.prevent="create">
                        <div class="space-y-6">

                            {{-- PERSONAL INFORMATION --}}
                            <div class="border rounded-lg p-4 space-y-3">
                                <h3 class="font-semibold text-gray-700">Employee Information</h3>

                                <x-input label="Name *" wire:model="userForm.name" />
                                <x-input label="Email *" wire:model="userForm.email" />
                                <x-input label="DTR Number *" wire:model="userForm.dtr_number" />

                                {{-- Gender --}}
                                <x-select.styled label="Gender *" wire:model="userForm.gender"
                                    placeholder="Select Gender" :options="[
                                        ['label' => 'Male', 'value' => 'male'],
                                        ['label' => 'Female', 'value' => 'female'],
                                        ['label' => 'Prefer not to say', 'value' => 'prefer_not'],
                                    ]" searchable />
                            </div>

                            {{-- ASSIGNMENT --}}
                            <div class="border rounded-lg p-4 space-y-3">
                                <h3 class="font-semibold text-gray-700">Assignment</h3>

                                <div class="grid sm:grid-cols-3 gap-4">
                                    <x-select.styled placeholder="Role" label="Role *" wire:model.live="role_id"
                                        :options="$this->roles" searchable />

                                    <x-select.styled placeholder="Section" label="Section *" wire:model.live="sectionId"
                                        :options="$this->sections" searchable />

                                    <x-select.styled placeholder="Unit" label="Unit *" wire:model.live="unitId"
                                        :options="$this->units" searchable />
                                </div>
                            </div>

                            {{-- JOB INFORMATION --}}
                            <div class="border rounded-lg p-4 space-y-3">
                                <h3 class="font-semibold text-gray-700">Job Information</h3>

                                <x-input label="Designation *" wire:model="userForm.designation" />
                                <x-input label="Office Position *" wire:model="userForm.office_position" />
                            </div>

                            {{-- SECURITY --}}
                            <div class="border rounded-lg p-4 space-y-3">
                                <h3 class="font-semibold text-gray-700">Security</h3>

                                <x-password label="Password *" wire:model="userForm.password" typing-only />
                                <x-password label="Confirm Password *" wire:model="userForm.password_confirmation" />
                            </div>

                        </div>

                        <div class="sm:py-4 flex justify-end">
                            <x-button submit icon="plus-circle">Submit</x-button>
                        </div>
                    </form>

                </x-tab.items>
                <x-tab.items tab="Guest">
                    <x-slot:left>
                        <x-icon name="user-circle" class="w-5 h-5" />
                    </x-slot:left>
                    <form wire:submit.prevent="createGuest">
                        <div class="border rounded-lg p-4 space-y-4">

                            <h3 class="font-semibold text-gray-700">Guest Information</h3>

                            <x-input label="Name *" wire:model="userForm.name" />
                            <x-input label="Email *" wire:model="userForm.email" />
                            <x-input label="Agency *" wire:model="agency" />

                        </div>

                        <div class="sm:py-4 flex justify-end">
                            <x-button submit icon="plus-circle">Submit</x-button>
                        </div>
                    </form>

                </x-tab.items>
            </x-tab>

        </x-modal>
    </div>
</div>

