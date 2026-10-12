<?php

namespace App\Actions\Requisition;

use App\Domain\Gasu\Aggregates\RequisitionAggregate;
use App\Models\Requisition;
use Illuminate\Support\Str;

class CreateRequestAction
{
    public function handle(array $data): Requisition
    {
        $uuid = (string) Str::uuid();

        RequisitionAggregate::retrieve($uuid)
            ->open($data['user_id'], $data['requested_by'] ?? null, $data['requested_date'] ?? null, $data['purpose'] ?? null)
            ->persist();

        return Requisition::where('uuid', $uuid)->firstOrFail();
    }
}
