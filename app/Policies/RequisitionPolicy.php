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

    /**
     * Generating the RIS and uploading the signed copy (which completes the
     * requisition) is open to the requisition's owner and to approvers only.
     * Unlike update(), the owner keeps this right after requested_by and
     * received_by are filled, since that is exactly when the RIS is signed.
     */
    public function complete(User $user, Requisition $requisition): bool
    {
        if ($user->can('approve-requisition')) {
            return true;
        }

        return $user->id === $requisition->user_id;
    }

    /**
     * Also gates setting approval/issuance details (approved_by, issued_by,
     * approved_date, issued_date) — see RequisitionForm::update().
     */
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
