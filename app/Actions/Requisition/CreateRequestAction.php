<?php

namespace App\Actions\Requisition;

use App\Models\Requisition;
use App\Models\RequisitionItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class CreateRequestAction
{
    public function handle(array $data): Requisition
    {
        return Requisition::create($data);
    }
}
