<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class UserPolicy
{
    /**
     * Roles that only a SUPER-ADMIN may grant, or modify a holder of.
     */
    private const PRIVILEGED_ROLES = ['ADMIN', 'SUPER-ADMIN'];

    public function viewAny(User $user): bool
    {
        return $user->can('manage-users');
    }

    public function view(User $user): bool
    {
        return $user->can('manage-users');
    }

    public function create(User $user): bool
    {
        return $user->can('manage-users');
    }

    /**
     * Without a target this is the generic "may manage users" check; with a
     * target it also protects privileged (ADMIN/SUPER-ADMIN) accounts.
     */
    public function update(User $user, ?User $target = null): bool
    {
        if ($target === null) {
            return $user->can('manage-users');
        }

        return $this->canModify($user, $target);
    }

    public function delete(User $user, User $target): bool
    {
        return $user->id !== $target->id && $this->canModify($user, $target);
    }

    public function changeRole(User $user, User $target, Role $newRole): bool
    {
        if ($user->id === $target->id || ! $this->canModify($user, $target)) {
            return false;
        }

        return $this->grantRole($user, $newRole);
    }

    /**
     * Whether the user may give $role to someone (e.g. when creating a user).
     */
    public function grantRole(User $user, Role $role): bool
    {
        if (! $user->can('manage-users')) {
            return false;
        }

        if (in_array($role->name, self::PRIVILEGED_ROLES, true)) {
            return $user->hasRole('SUPER-ADMIN');
        }

        return true;
    }

    /**
     * Only a SUPER-ADMIN may modify a user who currently holds ADMIN or SUPER-ADMIN.
     */
    private function canModify(User $user, User $target): bool
    {
        if (! $user->can('manage-users')) {
            return false;
        }

        if ($target->hasAnyRole(self::PRIVILEGED_ROLES)) {
            return $user->hasRole('SUPER-ADMIN');
        }

        return true;
    }
}
