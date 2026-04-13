<?php

namespace App\Actions\Procurement\PurchaseRequest;

use App\Models\PurchaseRequest;

class LoadMealDraft
{
    public function handle(PurchaseRequest $request): array
    {
        $saved = $request->mealLotBlocks()
            ->with(['items', 'accommodations.items'])
            ->orderBy('sort_order')
            ->get();

        if ($saved->isEmpty()) {
            return [];
        }

        return $saved->map(function ($lot) {
            return [
                'type' => 'lot',
                'lot_title' => $lot->lot_title,
                'location' => $lot->location,
                'date' => $lot->delivery_period,

                'items' => $lot->items->map(function ($i) {
                    return [
                        'id' => $i->id,
                        'pax_qty' => (int) $i->pax_qty,
                        'mealSnack' => $i->meal_snack,
                        'arrangement' => $i->arrangement,
                        'delivery_date' => $i->delivery_date,
                        'menu' => $i->menu,
                        'other_requirement' => $i->other_requirement,
                        'qty' => (float) $i->qty,
                        'unit' => $i->unit,
                        'estimated_unit_cost' => (float) $i->estimated_unit_cost,
                        'po_estimated_unit_cost' => $i->po_estimated_unit_cost !== null
                            ? (float) $i->po_estimated_unit_cost
                            : null,
                    ];
                })->toArray(),

                'accommodations' => $lot->accommodations->map(function ($a) {
                    return [
                        'type' => 'accommodation',
                        'accommodation_title' => $a->accommodation_title,
                        'location' => $a->location,
                        'date' => $a->date,
                        'items' => $a->items->map(function ($i) {
                            return [
                                'id' => $i->id,
                                'no_of_pax' => (int) $i->no_of_pax,
                                'room_requirement' => $i->room_requirement,
                                'no_of_rooms' => (int) $i->no_of_rooms,
                                'check_in' => $i->check_in,
                                'check_out' => $i->check_out,
                                'no_of_nights' => (int) $i->no_of_nights,
                                'other_requirement' => $i->other_requirement,
                                'qty' => (float) $i->qty,
                                'unit' => $i->unit,
                                'estimated_unit_cost' => (float) $i->estimated_unit_cost,
                                'po_estimated_unit_cost' => $i->po_estimated_unit_cost !== null
                                    ? (float) $i->po_estimated_unit_cost
                                    : null,
                            ];
                        })->toArray(),
                    ];
                })->toArray(),
            ];
        })->toArray();
    }
}
