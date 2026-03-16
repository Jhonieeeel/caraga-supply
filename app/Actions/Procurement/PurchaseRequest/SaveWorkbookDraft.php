<?php

namespace App\Actions\Procurement\PurchaseRequest;

use App\Models\PurchaseRequest;
use App\Models\PrWorkbookBlock;
use App\Models\PrWorkbookItem;
use Illuminate\Support\Facades\DB;

class SaveWorkbookDraft
{
    public function handle(PurchaseRequest $request, array $blocks): void
    {
        DB::transaction(function () use ($request, $blocks) {
            PrWorkbookBlock::where('purchase_request_id', $request->id)->delete();

            foreach ($blocks as $blockIndex => $block) {

                $wb = PrWorkbookBlock::create([
                    'purchase_request_id' => $request->id,
                    'sort_order' => $blockIndex,
                    'block_title' => $block['block_title'] ?? null,
                ]);

                foreach (($block['items'] ?? []) as $itemIndex => $item) {

                    PrWorkbookItem::create([
                        'workbook_block_id' => $wb->id,
                        'sort_order' => $itemIndex,
                        'particular' => $item['particular'] ?? null,
                        'delivery_date' => $item['delivery_date'] ?? null,
                        'qty' => $item['qty'] ?? 0,
                        'unit' => $item['unit'] ?? null,
                        'estimated_unit_cost' => $item['estimated_unit_cost'] ?? 0,
                    ]);
                }
            }
        });
    }
}
