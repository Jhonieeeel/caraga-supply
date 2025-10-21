<?php

namespace App\Actions\Procurement;

use App\Models\PurchaseRequest;

class CreateRequest {
    public function handle(array $data): PurchaseRequest {
        return PurchaseRequest::create($data);
    }
}
