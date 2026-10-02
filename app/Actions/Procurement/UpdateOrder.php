<?php

namespace App\Actions\Procurement;

use App\Models\Procurement;
use App\Models\PurchaseOrder;

class UpdateOrder
{
    public function handle(PurchaseOrder $purchaseOrder, array $data) {
         $oldPrice = (float) ($purchaseOrder->contract_price ?? 0);

         $purchaseOrder->update($data);

         $newPrice = (float) ($purchaseOrder->contract_price ?? 0);
         $delta = $newPrice - $oldPrice;

         if ($purchaseOrder->procurement_id && $delta !== 0.0) {
             $procurement = Procurement::find($purchaseOrder->procurement_id);

             if ($procurement && !is_null($procurement->remaining_budget)) {
                 $procurement->decrement('remaining_budget', $delta);
             }
         }

         return $purchaseOrder;
    }
}
