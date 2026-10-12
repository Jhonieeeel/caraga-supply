<?php

namespace App\Actions\Procurement;

use App\Models\Procurement;
use App\Models\PurchaseOrder;
use Illuminate\Validation\ValidationException;

class CreateOrder {
    public function handle(array $data): PurchaseOrder {
        // One PO per PR: a second PO would deduct remaining_budget again and be hidden by Procurement::purchaseOrder() (hasOne).
        if (!empty($data['purchase_request_id'])
            && PurchaseOrder::where('purchase_request_id', $data['purchase_request_id'])->exists()) {
            throw ValidationException::withMessages([
                'purchase_request_id' => 'This purchase request already has a purchase order.',
            ]);
        }

        $order = PurchaseOrder::create($data);

        if ($order->procurement_id && !is_null($order->contract_price)) {
            $procurement = Procurement::find($order->procurement_id);

            if ($procurement && !is_null($procurement->remaining_budget)) {
                $procurement->decrement('remaining_budget', (float) $order->contract_price);
            }
        }

        return $order;
    }
}
