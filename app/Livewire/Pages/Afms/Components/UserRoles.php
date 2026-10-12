<?php

namespace App\Livewire\Pages\Afms\Components;

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;

/**
 * SUPER-ADMIN only: see and change what each individual user can access.
 *
 * A user starts with whatever their role (and unit) gives them. Ticking a box
 * adds a direct permission; unticking one that comes from the role or unit
 * records it in the user's revoked_permissions so it is denied for them only.
 */
class UserRoles extends Component
{
    use WithPagination, Interactions;

    /**
     * Permission => what it opens in the app. view-dashboard is left out on
     * purpose: nothing checks it, so a box for it would change nothing.
     */
    public const ACCESS = [
        'manage-supply' => 'Supply',
        'manage-stock' => 'Stock',
        'create-requisition' => 'Requisition',
        'approve-requisition' => 'Approve / RSMI / RPCI',
        'manage-procurement' => 'PMU',
        'manage-users' => 'HRMU',
    ];

    /** Always has full access; editing it could lock everyone out. */
    public const LOCKED_ROLE = 'SUPER-ADMIN';

    public $search;
    public int $quantity = 10;

    public function mount(): void
    {
        $this->ensureSuperAdmin();
    }

    public function updatedSearch(): void
    {
        $this->resetPage('access-page');
    }

    public function togglePermission(int $userId, string $permission): void
    {
        $this->ensureSuperAdmin();

        abort_unless(array_key_exists($permission, self::ACCESS), 422);

        $user = User::findOrFail($userId);

        if (! $this->canEdit($user)) {
            $this->dialog()->error('Locked', 'SUPER-ADMIN accounts always have full access.')->send();
            return;
        }

        $label = self::ACCESS[$permission];
        $revoked = collect($user->revoked_permissions ?? []);

        if ($user->can($permission)) {
            if ($user->hasDirectPermission($permission)) {
                $user->revokePermissionTo($permission);
            }

            // Still allowed through the role or unit: deny it for this user.
            if ($user->fresh()->can($permission)) {
                $this->saveRevoked($user, $revoked->push($permission));
            }

            $message = "{$user->name} can no longer access {$label}.";
        } else {
            $this->saveRevoked($user, $revoked->reject(fn ($p) => $p === $permission));

            // Not covered by the role or unit: grant it to this user directly.
            if (! $user->fresh()->can($permission)) {
                $user->givePermissionTo($permission);
            }

            $message = "{$user->name} can now access {$label}.";
        }

        unset($this->users);

        $this->toast()->success('Access updated', $message)->send();
    }

    /** Drop every per-user change so the user gets exactly what their role gives. */
    public function resetToRole(int $userId): void
    {
        $this->ensureSuperAdmin();

        $user = User::findOrFail($userId);

        if (! $this->canEdit($user)) {
            return;
        }

        $user->syncPermissions([]);
        $user->forceFill(['revoked_permissions' => null])->save();

        unset($this->users);

        $this->toast()->success('Access reset', "{$user->name} now has their role's default access.")->send();
    }

    public function canEdit(User $user): bool
    {
        return ! $user->hasRole(self::LOCKED_ROLE);
    }

    public function isCustomised(User $user): bool
    {
        return $user->permissions->isNotEmpty() || ! empty($user->revoked_permissions);
    }

    #[Computed()]
    public function users()
    {
        return User::query()
            ->with(['roles', 'permissions', 'employee.unit'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate($this->quantity, pageName: 'access-page');
    }

    private function saveRevoked(User $user, $revoked): void
    {
        $user->forceFill(['revoked_permissions' => $revoked->unique()->values()->all() ?: null])->save();
    }

    private function ensureSuperAdmin(): void
    {
        abort_unless(auth()->user()?->hasRole(self::LOCKED_ROLE), 403);
    }

    public function render()
    {
        return view('livewire.pages.afms.components.user-roles', [
            'access' => self::ACCESS,
        ]);
    }
}
