<?php

namespace App\Actions\Procurement\PurchaseRequest;

use App\Models\PurchaseRequest;
use App\Models\PrMealLotBlock;
use App\Models\PrMealItem;
use App\Models\PrAccommodationBlock;
use App\Models\PrAccommodationItem;

class SaveMealDraft
{
    public function handle(PurchaseRequest $request, array $blocks): void
    {
        $request->mealLotBlocks()->delete();

        foreach ($blocks as $blockIndex => $block) {
            $lot = PrMealLotBlock::create([
                'purchase_request_id' => $request->id,
                'sort_order' => $blockIndex,
                'lot_title' => $block['lot_title'] ?? null,
                'location' => $block['location'] ?? null,
                'delivery_period'=> $block['date'] ?? null,
            ]);

            foreach (($block['items'] ?? []) as $itemIndex => $item) {
                PrMealItem::create([
                    'lot_block_id' => $lot->id,
                    'sort_order' => $itemIndex,
                    'pax_qty' => $item['pax_qty'] ?? 0,
                    'meal_snack' => $item['mealSnack'] ?? null,
                    'arrangement' => $item['arrangement'] ?? null,
                    'delivery_date' => $item['delivery_date'] ?? null,
                    'menu' => $item['menu'] ?? null,
                    'other_requirement' => $item['other_requirement'] ?? null,
                    'qty' => $item['qty'] ?? 0,
                    'unit' => $item['unit'] ?? null,
                    'estimated_unit_cost' => $item['estimated_unit_cost'] ?? 0,
                ]);
            }

            foreach (($block['accommodations'] ?? []) as $accIndex => $acc) {
                $accBlock = PrAccommodationBlock::create([
                    'lot_block_id' => $lot->id,
                    'sort_order' => $accIndex,
                    'accommodation_title' => $acc['accommodation_title'] ?? null,
                    'location' => $acc['location'] ?? null,
                    'date' => $acc['date'] ?? null,
                ]);

                foreach (($acc['items'] ?? []) as $accItemIndex => $accItem) {
                    PrAccommodationItem::create([
                        'accommodation_block_id' => $accBlock->id,
                        'sort_order' => $accItemIndex,
                        'no_of_pax' => $accItem['no_of_pax'] ?? 0,
                        'room_requirement' => $accItem['room_requirement'] ?? null,
                        'no_of_rooms' => $accItem['no_of_rooms'] ?? 0,
                        'check_in' => $accItem['check_in'] ?? null,
                        'check_out' => $accItem['check_out'] ?? null,
                        'no_of_nights' => $accItem['no_of_nights'] ?? 0,
                        'other_requirement' => $accItem['other_requirement'] ?? null,
                        'qty' => $accItem['qty'] ?? 0,
                        'unit' => $accItem['unit'] ?? null,
                        'estimated_unit_cost' => $accItem['estimated_unit_cost'] ?? 0,
                    ]);
                }
            }
        }
    }
}
