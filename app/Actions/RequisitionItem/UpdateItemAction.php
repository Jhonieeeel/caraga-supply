<?php

namespace App\Actions\RequisitionItem;

use App\Domain\Gasu\Aggregates\RequisitionAggregate;
use App\Domain\Gasu\Aggregates\SupplyAggregate;
use App\Models\RequisitionItem;
use Illuminate\Support\Facades\Auth;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Spatie\EventSourcing\AggregateRoots\Exceptions\CouldNotPersistAggregate;

class UpdateItemAction
{
    /**
     * Changes an item's requested quantity through the aggregates so the
     * stock lots stay in step: an increase is allocated (anchored on the
     * item's lot, spilling to the supply's other lots like a new request,
     * throwing InsufficientStockException if the supply cannot cover it), a
     * decrease is released back to the item's lot. Nothing is written to the
     * stocks/requisition_items tables directly.
     */
    public function handle(RequisitionItem $item, int $quantity): RequisitionItem
    {
        if ($quantity < 1) {
            throw new \InvalidArgumentException('Requested quantity must be at least 1.');
        }

        $item->loadMissing('stock.supply', 'requisition');

        $delta = $quantity - (int) $item->requested_qty;

        if ($delta === 0) {
            return $item;
        }

        $supplyUuid = $item->stock->supply->uuid;
        $stockNumber = $item->stock->stock_number;
        $requisitionUuid = $item->requisition->uuid;

        retry(3, function () use ($supplyUuid, $stockNumber, $requisitionUuid, $delta) {
            $supply = SupplyAggregate::retrieve($supplyUuid);
            $requisition = RequisitionAggregate::retrieve($requisitionUuid);

            if ($delta > 0) {
                foreach ($supply->allocate($stockNumber, $delta, $requisitionUuid) as $lot => $allocated) {
                    $requisition->recordAllocation($supplyUuid, (string) $lot, $allocated);
                }
            } else {
                foreach ($supply->releaseAllocation($requisitionUuid, $stockNumber, -$delta, Auth::id()) as $lot => $released) {
                    $requisition->recordAllocationRelease($supplyUuid, (string) $lot, $released);
                }
            }

            AggregateRoot::persistInTransaction($supply, $requisition);
        }, 50, fn (\Throwable $e) => $e instanceof CouldNotPersistAggregate);

        return $item->fresh() ?? $item;
    }
}
