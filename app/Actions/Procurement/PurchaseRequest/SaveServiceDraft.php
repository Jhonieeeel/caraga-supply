<?php

namespace App\Actions\Procurement\PurchaseRequest;

use App\Models\PrService;
use App\Models\PurchaseRequest;

class SaveServiceDraft
{
    public function handle(PurchaseRequest $request, array $data): void
    {
        PrService::updateOrCreate(
            ['purchase_request_id' => $request->id],
            [
                'delivery_period' => $data['delivery_period'] ?? null,
                'delivery_site' => $data['delivery_site'] ?? null,
                'quantity' => $data['quantity'] ?? 0,
                'unit' => $data['unit'] ?? null,
                'estimated_unit_cost' => $data['estimated_unit_cost'] ?? 0,
                'technical_specifications' => $data['technical_specifications'] ?? null,
            ]
        );
    }
}
