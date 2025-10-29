<?php

namespace App\Actions\Procurement;

use App\Models\PurchaseOrder;

class UpdateOrder
{
    public function handle(PurchaseOrder $purchaseOrder, array $data) {
        $purchaseOrder->update($data);

        return $purchaseOrder;
    }
}
