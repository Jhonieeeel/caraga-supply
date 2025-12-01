<div class="space-y-6 shadow-sm">

    @php
        $disabled = !auth()->user()->hasRole('Super Admin');
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
                                color="{{ $role->name === 'Super Admin' ? 'blue' : 'gray' }}" />
                        @endforeach
                    </div>
                </div>
            </div>

            @hasrole('User')
                <div></div>
            @endrole

            auth()->user()-hasRole()

            {{-- FORM: Profile Update --}}
            <form wire:submit.prevent="updateUserInfo " class="space-y-3 pt-4 border-t">

                <h3 class="text-sm font-semibold text-gray-800">Profile Information</h3>

                <div class="grid sm:grid-cols-2 gap-4">

                    {{-- Name --}}
                    <x-input label="Name *" hint="Update your name" wire:model="userForm.name" :disabled="$disabled" />

                    {{-- Email --}}
                    <x-input label="Email *" hint="Update your email" wire:model="userForm.email" :disabled="$disabled" />

                    {{-- Role (Super Admin only) --}}
                    {{-- @role('Super Admin')
                        <x-select.styled label="Role *" hint="Update user role" placeholder="Select role" :options="$this->roles"
                            searchable />
                    @endrole --}}
                </div>

                <div class="pt-3 flex justify-end">
                    <x-button text="Save Changes" submit />
                </div>
            </form>
        </div>

        <div class="max-w-7xl mx-auto sm:px-6 py-6 bg-white border shadow rounded-lg space-y-4">

            <div>
                <h2 class="text-lg font-semibold text-gray-900">Update Password</h2>
                <p class="text-sm text-gray-600">
                    Ensure your password is strong and secure.
                </p>
            </div>

            <div class="grid sm:grid-cols-2 gap-4 pt-2">

                {{-- Current Password --}}
                <x-password label="Current Password *" wire:model="userForm.password" :disabled="$disabled" typing-only />

                {{-- New Password --}}
                <x-password label="New Password *" wire:model="userForm.new_password" :disabled="$disabled" typing-only />

                {{-- Confirm --}}
                <x-password label="Confirm Password *" wire:model="userForm.new_password_confirmation" :disabled="$disabled"
                    typing-only />

            </div>
            <div class="pt-3 flex justify-end">
                <x-button text="Update Password" submit />
            </div>
        </div>
    @else
        {{-- EMPTY STATE --}}
        <div class="max-w-7xl mx-auto sm:px-6 py-6 bg-white border shadow rounded-lg">
            <p class="text-center text-sm text-gray-500">
                Select a user to view details.
            </p>
        </div>
    @endif
</div>

