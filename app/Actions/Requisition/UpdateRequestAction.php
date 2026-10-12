<?php

namespace App\Actions\Requisition;

use App\Domain\Gasu\Aggregates\RequisitionAggregate;
use App\Models\Requisition;
use Illuminate\Support\Facades\Auth;

class UpdateRequestAction
{
    public function handle(Requisition $requisition, array $data): Requisition
    {
        RequisitionAggregate::retrieve($requisition->uuid)
            ->updateDetails($data, Auth::id())
            ->persist();

        return $requisition->fresh();
    }
}
