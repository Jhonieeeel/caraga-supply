<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class UserPolicy
{
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

    public function update(User $user): bool
    {
        return $user->can('manage-users');
    }

    public function delete(User $user, User $target): bool
    {
        return $user->can('manage-users') && $user->id !== $target->id;
    }

    public function changeRole(User $user, User $target, Role $newRole): bool
    {
        if (! $user->can('manage-users') || $user->id === $target->id) {
            return false;
        }

        if (in_array($newRole->name, ['ADMIN', 'SUPER-ADMIN'], true)) {
            return $user->hasRole('SUPER-ADMIN');
        }

        return true;
    }
}
