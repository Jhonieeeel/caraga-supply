<div class="space-y-6 shadow-sm">

    @php
        $disabled = !auth()->user()->can('manage-users');
    @endphp

    @if ($user)

        <!-- #region -->
        <div class="max-w-7xl mx-auto sm:px-6 py-6 bg-white border shadow rounded-lg space-y-6">

            {{-- Header --}}
            <div class="flex gap-4 items-center">
                <img src="{{ asset('illustrators/' . ($user->gender === 'Male' ? 'male_avatar.svg' : 'female_avatar.svg')) }}"
                    class="w-20 h-20 rounded-full object-cover" alt="User Avatar">

                <div class="space-y-1">
                    <h5 class="text-base font-semibold text-gray-900 capitalize">{{ $user->name }}</h5>
                    <p class="text-xs text-gray-500">{{ $user->email }}</p>

                    <div class="flex flex-wrap gap-1">
                        @foreach ($user->roles as $role)
                            <x-badge text="{{ $role->name }}"
                                color="{{ in_array($role->name, ['SUPER-ADMIN', 'ADMIN'], true) ? 'blue' : 'gray' }}" />
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- FORM: Profile Update --}}
            <form wire:submit.prevent="updateUserInfo " class="space-y-3 pt-4 border-t">

                <h3 class="text-sm font-semibold text-gray-800">Profile Information</h3>

                <div class="grid sm:grid-cols-2 gap-4">

                    {{-- Name --}}
                    <x-input label="Name *" hint="Update your name" wire:model="userForm.name" :disabled="$disabled" />

                    {{-- Email --}}
                    <x-input label="Email *" hint="Update your email" wire:model="userForm.email" :disabled="$disabled" />
                </div>

                <div class="pt-3 flex justify-end">
                    <x-button text="Save Changes" submit />
                </div>
            </form>
        </div>

        {{-- Assignment (manage-users only) --}}
        @can('manage-users')
            @if ($user->employee)
                <div class="max-w-7xl mx-auto sm:px-6 py-6 bg-white border shadow rounded-lg space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Assignment</h2>
                        <p class="text-sm text-gray-600">
                            Update this user's section, unit, and division.
                        </p>
                    </div>

                    <form wire:submit.prevent="updateAssignment" class="space-y-4">
                        <div class="grid sm:grid-cols-2 gap-4">
                            <x-input placeholder="Section" label="Section *" wire:model="sectionName" />
                            <x-input placeholder="Unit" label="Unit *" wire:model="unitName" />
                            <x-input placeholder="Division" label="Division *" wire:model="divisionName" />
                        </div>
                        <div class="pt-3 flex justify-end">
                            <x-button text="Update Assignment" submit />
                        </div>
                    </form>
                </div>
            @endif
        @endcan

        {{-- Role (manage-users only) --}}
        @can('manage-users')
            <div class="max-w-7xl mx-auto sm:px-6 py-6 bg-white border shadow rounded-lg space-y-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Role</h2>
                    <p class="text-sm text-gray-600">
                        Promote or change this user's role.
                    </p>
                </div>

                <form wire:submit.prevent="updateRole" class="sm:flex items-end gap-4">
                    <div class="flex-1">
                        <x-select.styled label="Role *" hint="Update user role" placeholder="Select role"
                            wire:model="role_id" :options="$this->roles" searchable />
                    </div>
                    <div class="pt-3 sm:pt-0">
                        <x-button text="Update Role" submit />
                    </div>
                </form>
            </div>
        @endcan

        <form wire:submit.prevent="updatePassword" class="max-w-7xl mx-auto sm:px-6 py-6 bg-white border shadow rounded-lg space-y-4">

            <div>
                <h2 class="text-lg font-semibold text-gray-900">Update Password</h2>
                <p class="text-sm text-gray-600">
                    Ensure your password is strong and secure.
                </p>
            </div>

            <div class="grid sm:grid-cols-2 gap-4 pt-2">

                <x-password label="Current Password *" wire:model="userForm.current_password" />
                <x-password label="New Password *" wire:model="userForm.new_password" />
                <x-password label="Confirm Password *" wire:model="userForm.new_password_confirmation" />

            </div>
            <div class="pt-3 flex justify-end">
                <x-button text="Update Password" submit />
            </div>
        </form>
    @else
        {{-- EMPTY STATE --}}
        <div class="max-w-7xl mx-auto sm:px-6 py-6 bg-white border shadow rounded-lg">
            <p class="text-center text-sm text-gray-500">
                Select a user to view details.
            </p>
        </div>
    @endif
</div>

