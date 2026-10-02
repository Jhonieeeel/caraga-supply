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
     *                                  All lines are allocated atomically:
     *                                  if any line fails, none persists.
     */
    public function handle(Requisition $requisition, array $data): Collection
    {
        $lines = [];

        foreach ($data as $stockId => $qty) {
            if ((int) $qty === 0) {
                continue;
            }

            $stock = Stock::with('supply')->findOrFail($stockId);

            $lines[] = [$stock->supply->uuid, $stock->stock_number, (int) $qty];
        }

        if ($lines) {
            $this->handler->handleMany($lines, $requisition->uuid);
        }

        return $requisition->refresh()->items;
    }
}
