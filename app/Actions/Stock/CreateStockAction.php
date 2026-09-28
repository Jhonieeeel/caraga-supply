<?php

namespace App\Actions\Stock;

use App\Domain\Gasu\Aggregates\SupplyAggregate;
use App\Models\Stock;
use App\Models\Supply;
use Illuminate\Support\Facades\Auth;

class CreateStockAction
{
    public function handle(array $data): Stock
    {
        $supply = Supply::findOrFail($data['supply_id']);

        SupplyAggregate::retrieve($supply->uuid)
            ->addLot(
                stockNumber: $data['stock_number'],
                barcode: $data['barcode'] ?? null,
                stockLocation: $data['stock_location'] ?? null,
                price: (float) ($data['price'] ?? 0),
                initialQuantity: (int) ($data['quantity'] ?? 0),
                occurredBy: Auth::id(),
            )
            ->persist();

        return Stock::where('supply_id', $supply->id)
            ->where('stock_number', $data['stock_number'])
            ->firstOrFail();
    }
}
