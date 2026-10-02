<?php

namespace App\Actions\Stock;

use App\Domain\Gasu\Aggregates\SupplyAggregate;
use App\Domain\Gasu\Exceptions\StockLotInUseException;
use App\Models\Stock;
use Illuminate\Support\Facades\Auth;

class RemoveStockAction
{
    /**
     * Removes a stock lot through the SupplyAggregate (StockLotRemoved) so the
     * aggregate never keeps allocating from a lot whose row is gone. Only
     * lots that were never requested can be removed; anything referenced by a
     * requisition throws StockLotInUseException and nothing is recorded.
     */
    public function handle(Stock $stock): void
    {
        if ($stock->items()->exists()) {
            throw StockLotInUseException::hasAllocations($stock->stock_number);
        }

        SupplyAggregate::retrieve($stock->supply->uuid)
            ->removeLot($stock->stock_number, Auth::id())
            ->persist();
    }
}
