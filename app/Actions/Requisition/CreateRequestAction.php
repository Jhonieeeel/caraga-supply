<?php

namespace App\Actions\Requisition;

use App\Models\Requisition;

class CreateRequestAction
{
    public function handle(array $data): Requisition
    {
        return Requisition::create($data);
    }
}
