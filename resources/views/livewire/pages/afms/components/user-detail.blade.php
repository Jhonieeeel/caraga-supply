<div class="space-y-4">
    @if ($user)
        <div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8 bg-white border shadow rounded">
            <div class="flex gap-4 items-center ">
                <img src="{{ asset('illustrators/' . ($user->gender === 'Male' ? 'male_avatar.svg' : 'female_avatar.svg')) }}"
                    class="w-20" alt="">
                <div>
                    <h5 class="font-semibold text-sm text-gray-800 capitalize">{{ $user->name }}</h5>
                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                    <span class="inline-flex flex-wrap">
                        @foreach ($user->roles as $role)
                            <x-badge text="{{ $role->name }}" color="blue" />
                        @endforeach
                    </span>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-7 my-6">
                <div class="sm:col-span-2">
                    <x-input wire:model="userForm.name" label="Name *" hint="Update your name" />
                </div>
                <div class="sm:col-span-2">
                    <x-input wire:model="userForm.email" label="Email *" hint="Update your email" />
                </div>
                <div class="sm:col-span-2">
                    <x-button text="Save" submit />
                </div>
            </div>
        </div>
        <div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8 bg-white border shadow rounded">
            <h2 class="text-lg font-medium text-gray-900">
                Update Password
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Ensure your account is using a long, random password to stay secure.
            </p>
            <div class="grid grid-cols-3 gap-7 my-6">
                <div class="sm:col-span-2">
                    <x-password wire:model="userForm.password" value="{{ $userForm->password }}" typing-only
                        label="Current Password *" />
                </div>
                <div class="sm:col-span-2">
                    <x-password wire:model="userForm.password" value="{{ $userForm->password }}" typing-only
                        label="New Password *" />
                </div>
                <div class="sm:col-span-2">
                    <x-password wire:model="userForm.password" typing-only label="Confirm Password" />
                </div>
                <div class="sm:col-span-2">
                    <x-button text="Save" submit />
                </div>
            </div>
        </div>
    @else
        <div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8 bg-white border shadow rounded">
            <p class="text-center text-xs text-gray-500">
                Select a user to view details.
            </p>
        </div>
    @endif
</div>

