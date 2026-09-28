<?php

namespace App\Actions\RequisitionItem;

use App\Domain\Gasu\Commands\AllocateStockToRequisitionHandler;
use App\Models\Requisition;
use App\Models\Stock;
use Illuminate\Support\Collection;

class AllocateStockToRequisitionAction
{
    public function __construct(private readonly AllocateStockToRequisitionHandler $handler)
    {
    }

    /**
     * @param  array<int, int>  $data  stock_id => quantity requested. Each
     *                                  stock_id is the anchor lot the user
     *                                  picked in the UI; if it doesn't have
     *                                  enough quantity, the handler spills
     *                                  the remainder across the same
     *                                  supply's other lots automatically.
     */
    public function handle(Requisition $requisition, array $data): Collection
    {
        foreach ($data as $stockId => $qty) {
            if ((int) $qty === 0) {
                continue;
            }

            $stock = Stock::findOrFail($stockId);
            $supply = $stock->supply;

            $this->handler->handle($supply->uuid, $stock->stock_number, (int) $qty, $requisition->uuid);
        }

        return $requisition->refresh()->items;
    }
}
