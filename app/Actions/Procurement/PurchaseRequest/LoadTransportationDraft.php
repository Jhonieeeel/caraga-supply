<?php

namespace App\Actions\Procurement\PurchaseRequest;

use App\Models\PurchaseRequest;

class LoadTransportationDraft
{
    public function handle(PurchaseRequest $request): array
    {
        $saved = $request->transportation()->with('items')->first();

        if (!$saved) {
            return [];
        }

        return [
            'type' => 'transportation',
            'delivery_period' => $saved->delivery_period,
            'delivery_site' => $saved->delivery_site,
            'pick_up' => $saved->pick_up,
            'reqs_vehicle' => $saved->reqs_vehicle,
            'reqs_model' => $saved->reqs_model,
            'reqs_number' => $saved->reqs_number,

            'items' => $saved->items->map(fn ($i) => [
                'pax_qty' => (int) $i->pax_qty,
                'itinerary' => $i->itinerary,
                'date_time' => $i->date_time,
                'no_of_vehicles' => (int) $i->no_of_vehicles,
                'estimated_unit_cost' => (float) $i->estimated_unit_cost,
            ])->toArray(),
        ];
    }
}
