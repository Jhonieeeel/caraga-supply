<?php

namespace App\Actions\Stock;

use App\Models\Requisition;
use App\Models\RequisitionItem;
use Illuminate\Support\Facades\DB;

class UpdateStockQuantity
{
    public function handle(Requisition $requisition): Requisition
    {
        $items = RequisitionItem::where('requisition_id', $requisition->id)->get();

        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                if ($item->stock) {
                    $item->stock->decrement('quantity', $item->requested_qty);
                }
            }
        });

        return $requisition;
    }
}
