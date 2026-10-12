<?php

namespace App\Actions\Stock;

use App\Domain\Gasu\Aggregates\SupplyAggregate;
use App\Models\Stock;
use Illuminate\Support\Facades\Auth;

class EditStockAction
{
    /**
     * Metadata only (barcode/location/price) — quantity is never touched
     * here; it moves exclusively through receiveStock()/allocate() so every
     * quantity change stays part of the audited event stream.
     */
    public function handle(Stock $stock, array $data): Stock
    {
        $supply = $stock->supply;

        SupplyAggregate::retrieve($supply->uuid)
            ->updateLotDetails(
                stockNumber: $stock->stock_number,
                barcode: $data['barcode'] ?? $stock->barcode,
                stockLocation: $data['stock_location'] ?? $stock->stock_location,
                price: (float) ($data['price'] ?? $stock->price),
                occurredBy: Auth::id(),
            )
            ->persist();

        return $stock->fresh();
    }
}
