<?php

namespace App\Actions\Procurement;

use App\Models\Procurement;

class CreateRequest {
    public function handle(Procurement $procurement, array $data): Procurement
    {
        $procurement->update($data);

        return $procurement;
    }
}
