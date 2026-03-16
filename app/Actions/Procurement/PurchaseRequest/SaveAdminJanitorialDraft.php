<?php

namespace App\Actions\Procurement\PurchaseRequest;

use App\Models\PrAdminJanitorialBlock;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\DB;

class SaveAdminJanitorialDraft
{
    /**
     * $data shape:
     * [
     *   'delivery_period' => '...',
     *   'delivery_site'   => '...',
     *   'blocks'          => [
     *     ['block_title' => '...', 'administrative_items' => [...], 'janitorial_items' => [...]],
     *     ...
     *   ]
     * ]
     */
    public function handle(PurchaseRequest $request, array $data): void
    {
        DB::transaction(function () use ($request, $data) {
            // Wipe existing (cascade deletes items)
            PrAdminJanitorialBlock::where('purchase_request_id', $request->id)->delete();

            $deliveryPeriod = $data['delivery_period'] ?? '';
            $deliverySite   = $data['delivery_site'] ?? '';

            foreach ($data['blocks'] as $sortOrder => $blockData) {
                $block = PrAdminJanitorialBlock::create([
                    'purchase_request_id' => $request->id,
                    'block_title'         => $blockData['block_title'] ?? '',
                    'delivery_period'     => $deliveryPeriod,
                    'delivery_site'       => $deliverySite,
                    'sort_order'          => $sortOrder,
                ]);

                foreach ($blockData['administrative_items'] ?? [] as $itemOrder => $item) {
                    $block->items()->create([
                        'item_group'          => 'administrative',
                        'item_name'           => $item['item_name'] ?? '',
                        'quantity'            => $item['quantity'] ?? 0,
                        'unit'                => $item['unit'] ?? '',
                        'estimated_unit_cost' => $item['estimated_unit_cost'] ?? 0,
                        'sort_order'          => $itemOrder,
                    ]);
                }

                foreach ($blockData['janitorial_items'] ?? [] as $itemOrder => $item) {
                    $block->items()->create([
                        'item_group'          => 'janitorial',
                        'item_name'           => $item['item_name'] ?? '',
                        'quantity'            => $item['quantity'] ?? 0,
                        'unit'                => $item['unit'] ?? '',
                        'estimated_unit_cost' => $item['estimated_unit_cost'] ?? 0,
                        'sort_order'          => $itemOrder,
                    ]);
                }
            }
        });
    }
}
