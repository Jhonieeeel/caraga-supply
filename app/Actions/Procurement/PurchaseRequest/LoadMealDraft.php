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
                        'pax_qty' => (int) $i->pax_qty,
                        'mealSnack' => $i->meal_snack,
                        'arrangement' => $i->arrangement,
                        'delivery_date' => $i->delivery_date,
                        'menu' => $i->menu,
                        'other_requirement' => $i->other_requirement,
                        'qty' => (float) $i->qty,
                        'unit' => $i->unit,
                        'estimated_unit_cost' => (float) $i->estimated_unit_cost,
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
                                'no_days' => (int) $i->no_days,
                                'room_type' => $i->room_type,
                                'room_arrangement' => $i->room_arrangement,
                                'inclusive_dates' => $i->inclusive_dates,
                                'remarks' => $i->remarks,
                                'other_requirement' => $i->other_requirement,
                                'qty' => (float) $i->qty,
                                'unit' => $i->unit,
                                'estimated_unit_cost' => (float) $i->estimated_unit_cost,
                            ];
                        })->toArray(),
                    ];
                })->toArray(),
            ];
        })->toArray();
    }
}
