<?php

namespace App\Actions\Procurement\PurchaseRequest;

use App\Models\PurchaseRequest;

class LoadServiceDraft
{
    public function handle(PurchaseRequest $request): array
    {
        $saved = $request->service()->first();

        if (!$saved) {
            return [];
        }

        return [
            'type' => 'service',
            'delivery_period' => $saved->delivery_period,
            'delivery_site' => $saved->delivery_site,
            'quantity' => (int) $saved->quantity,
            'unit' => $saved->unit,
            'estimated_unit_cost' => (float) $saved->estimated_unit_cost,
            'technical_specifications' => $saved->technical_specifications,
        ];
    }
}
