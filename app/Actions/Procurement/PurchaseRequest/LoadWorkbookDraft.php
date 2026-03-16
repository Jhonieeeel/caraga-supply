<?php

namespace App\Actions\Procurement\PurchaseRequest;

use App\Models\PurchaseRequest;
use App\Models\PrWorkbookBlock;

class LoadWorkbookDraft
{
    public function handle(PurchaseRequest $request): array
    {
        $saved = PrWorkbookBlock::query()
            ->where('purchase_request_id', $request->id)
            ->with(['items' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        if ($saved->isEmpty()) {
            return [];
        }

        return $saved->map(function ($b) {
            return [
                'type' => 'workbook',
                'block_title' => $b->block_title,
                'items' => $b->items->map(function ($i) {
                    return [
                        'particular' => $i->particular,
                        'delivery_date' => $i->delivery_date,
                        'qty' => (float) $i->qty,
                        'unit' => $i->unit,
                        'estimated_unit_cost' => (float) $i->estimated_unit_cost,
                    ];
                })->toArray(),
            ];
        })->toArray();
    }
}
