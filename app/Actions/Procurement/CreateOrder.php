<?php

namespace App\Actions\Procurement;

use App\Models\PurchaseOrder;

class CreateOrder {
    public function handle(array $data): PurchaseOrder {
        return PurchaseOrder::create($data);
    }
}
