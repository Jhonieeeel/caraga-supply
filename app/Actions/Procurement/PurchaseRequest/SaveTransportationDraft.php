<?php

namespace App\Actions\Procurement\PurchaseRequest;

use App\Models\PrTransportation;
use App\Models\PrTransportationItem;
use App\Models\PurchaseRequest;

class SaveTransportationDraft
{
    public function handle(PurchaseRequest $request, array $data): void
    {
        $transportation = PrTransportation::updateOrCreate(
            ['purchase_request_id' => $request->id],
            [
                'delivery_period' => $data['delivery_period'] ?? null,
                'delivery_site' => $data['delivery_site'] ?? null,
                'pick_up' => $data['pick_up'] ?? null,
                'reqs_vehicle' => $data['reqs_vehicle'] ?? null,
                'reqs_model' => $data['reqs_model'] ?? null,
                'reqs_number' => $data['reqs_number'] ?? null,
            ]
        );

        $transportation->items()->delete();

        foreach ($data['items'] ?? [] as $sortOrder => $item) {
            PrTransportationItem::create([
                'pr_transportation_id' => $transportation->id,
                'pax_qty' => $item['pax_qty'] ?? 0,
                'itinerary' => $item['itinerary'] ?? null,
                'date_time' => $item['date_time'] ?? null,
                'no_of_vehicles' => $item['no_of_vehicles'] ?? 0,
                'estimated_unit_cost' => $item['estimated_unit_cost'] ?? 0,
                'sort_order' => $sortOrder,
            ]);
        }
    }
}
