<?php

namespace App\Actions\Procurement;

use App\Models\Procurement;

class UpdateAnnual
{
    public function handle(Procurement $procurement, array $data): Procurement
    {
        $procurement->update($data);

        return $procurement;
    }
}
