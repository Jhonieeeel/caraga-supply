<?php

namespace App\Policies;

use App\Models\Requisition;
use App\Models\User;

class RequisitionPolicy
{
    public function create(User $user): bool
    {
        return $user->can('create-requisition');
    }

    public function update(User $user, Requisition $requisition): bool
    {
        if ($user->can('approve-requisition')) {
            return true;
        }

        $isOwner = $user->id === $requisition->user_id;
        $twoFieldsFilled = (bool) ($requisition->requested_by && $requisition->received_by);

        return $isOwner && ! $twoFieldsFilled;
    }

    public function approve(User $user, Requisition $requisition): bool
    {
        return $user->can('approve-requisition');
    }

    public function delete(User $user, Requisition $requisition): bool
    {
        if ($user->can('approve-requisition')) {
            return true;
        }

        return $user->id === $requisition->user_id && ! $requisition->completed;
    }
}
