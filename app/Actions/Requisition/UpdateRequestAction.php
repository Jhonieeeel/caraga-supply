<?php

namespace App\Actions\Requisition;

use App\Models\Requisition;

class UpdateRequestAction
{
    public function handle(Requisition $requisition, array $data): Requisition
    {
        $requisition->update($data);
        return $requisition;
    }
}
