<?php

namespace App\Actions\Rsmi;

use App\Models\Requisition;

class CreateRsmiAction
{
    public function handle(Requisition $requisition)
    {
        dd($requisition);
    }
}
