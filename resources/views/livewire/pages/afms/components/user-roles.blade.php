<div class="p-2 space-y-3">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
        <div>
            <h3 class="text-lg font-bold text-gray-800">User Access</h3>
            <p class="text-sm text-gray-500">
                Tick or untick a box to change what that one user can open. Changes apply immediately.
                Users marked <span class="font-semibold text-amber-700">Custom</span> differ from their role's default.
            </p>
        </div>
        <div class="sm:w-64">
            <x-input wire:model.live.debounce.300ms="search" placeholder="Search name" icon="magnifying-glass" />
        </div>
    </div>

    <div class="overflow-x-auto rounded-sm shadow ring-1 ring-gray-300">
        <table class="min-w-full text-sm">
            <thead class="bg-primary-900">
                <tr>
                    <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-100">Name</th>
                    <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-100">Role</th>
                    <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-100">Unit</th>
                    @foreach ($access as $label)
                        <th class="px-3 py-3.5 text-center text-xs font-semibold text-gray-100 whitespace-nowrap">{{ $label }}</th>
                    @endforeach
                    <th class="px-3 py-3.5 text-xs font-semibold text-gray-100"><span class="sr-only">Reset</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($this->users as $user)
                    @php
                        $editable = $this->canEdit($user);
                        $custom = $this->isCustomised($user);
                    @endphp
                    <tr wire:key="user-access-{{ $user->id }}">
                        <td class="px-3 py-2.5 text-gray-700 whitespace-nowrap">
                            {{ $user->name }}
                            @if ($custom)
                                <span class="ml-1 rounded bg-amber-100 px-1.5 py-0.5 text-xs font-semibold text-amber-700">Custom</span>
                            @endif
                            @unless ($editable)
                                <span class="ml-1 text-xs text-gray-400">(always full access)</span>
                            @endunless
                        </td>
                        <td class="px-3 py-2.5 text-gray-700 whitespace-nowrap">{{ $user->roles->pluck('name')->join(', ') ?: '—' }}</td>
                        <td class="px-3 py-2.5 text-gray-700 whitespace-nowrap">{{ $user->employee?->unit?->name ?? '—' }}</td>
                        @foreach ($access as $permission => $label)
                            <td class="px-3 py-2.5 text-center">
                                <input type="checkbox"
                                    aria-label="{{ $user->name }}: {{ $label }}"
                                    class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 disabled:opacity-50"
                                    @checked($user->can($permission))
                                    @disabled(! $editable)
                                    wire:click="togglePermission({{ $user->id }}, '{{ $permission }}')"
                                    wire:loading.attr="disabled" />
                            </td>
                        @endforeach
                        <td class="px-3 py-2.5 text-right whitespace-nowrap">
                            @if ($editable && $custom)
                                <x-button.circle xs flat color="secondary" icon="arrow-uturn-left"
                                    title="Reset to role default"
                                    wire:click="resetToRole({{ $user->id }})" />
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 4 + count($access) }}" class="px-3 py-6 text-center text-gray-500">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $this->users->links() }}
</div>
