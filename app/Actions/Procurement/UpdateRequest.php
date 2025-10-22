<?php

namespace App\Actions\Procurement;

use App\Models\PurchaseRequest;

class UpdateRequest
{
    public function handle(PurchaseRequest $purchaseRequest, array $data) {
        $purchaseRequest->update($data);

        return $purchaseRequest
    }
}
