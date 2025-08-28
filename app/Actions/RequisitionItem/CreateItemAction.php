<?php

namespace App\Actions\RequisitionItem;

use App\Models\Requisition;
use App\Models\RequisitionItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CreateItemAction
{
    public function handle(Requisition $requisition, array $data): Collection
    {
        DB::transaction(function () use ($requisition, $data) {
            foreach ($data as $stock_id => $qty) {
                $items = $requisition->items()->where('stock_id', $stock_id)->first();
                if ($qty != 0) {
                    RequisitionItem::updateOrCreate(
                        [
                            'stock_id' => $stock_id,
                            'requisition_id' => $requisition->id,
                        ],
                        [
                            'requested_qty' => $items ? $items->requested_qty + (int) $qty : (int) $qty,
                        ]
                    );
                }
            }
        });

        return $requisition->refresh()->items;
    }
}
