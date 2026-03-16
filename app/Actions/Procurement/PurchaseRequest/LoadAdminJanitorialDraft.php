<?php

namespace App\Actions\Procurement\PurchaseRequest;

use App\Models\PrAdminJanitorialBlock;
use App\Models\PurchaseRequest;

class LoadAdminJanitorialDraft
{
    public function handle(PurchaseRequest $request): array
    {
        $blocks = PrAdminJanitorialBlock::where('purchase_request_id', $request->id)
            ->with(['administrativeItems', 'janitorialItems'])
            ->orderBy('sort_order')
            ->get();

        if ($blocks->isEmpty()) {
            return [];
        }

        $first = $blocks->first();

        return [
            'delivery_period' => $first->delivery_period ?? '',
            'delivery_site'   => $first->delivery_site ?? '',
            'blocks'          => $blocks->map(fn ($block) => [
                'block_title'          => $block->block_title ?? '',
                'administrative_items' => $block->administrativeItems->map(fn ($item) => [
                    'item_name'           => $item->item_name,
                    'quantity'            => $item->quantity,
                    'unit'                => $item->unit,
                    'estimated_unit_cost' => $item->estimated_unit_cost,
                ])->toArray(),
                'janitorial_items'     => $block->janitorialItems->map(fn ($item) => [
                    'item_name'           => $item->item_name,
                    'quantity'            => $item->quantity,
                    'unit'                => $item->unit,
                    'estimated_unit_cost' => $item->estimated_unit_cost,
                ])->toArray(),
            ])->toArray(),
        ];
    }
}
