<?php

namespace App\Actions\RequisitionItem;

use App\Domain\Gasu\Aggregates\RequisitionAggregate;
use App\Domain\Gasu\Aggregates\SupplyAggregate;
use App\Models\RequisitionItem;
use Illuminate\Support\Facades\Auth;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Spatie\EventSourcing\AggregateRoots\Exceptions\CouldNotPersistAggregate;

class RemoveItemAction
{
    /**
     * Releases the item's whole allocation back to its lot; the projector
     * removes the requisition_items row once nothing is left allocated.
     */
    public function handle(RequisitionItem $item): void
    {
        $item->loadMissing('stock.supply', 'requisition');

        $supplyUuid = $item->stock->supply->uuid;
        $stockNumber = $item->stock->stock_number;
        $requisitionUuid = $item->requisition->uuid;

        retry(3, function () use ($supplyUuid, $stockNumber, $requisitionUuid) {
            $supply = SupplyAggregate::retrieve($supplyUuid);
            $requisition = RequisitionAggregate::retrieve($requisitionUuid);

            foreach ($supply->releaseAllocation($requisitionUuid, $stockNumber, null, Auth::id()) as $lot => $released) {
                $requisition->recordAllocationRelease($supplyUuid, (string) $lot, $released);
            }

            AggregateRoot::persistInTransaction($supply, $requisition);
        }, 50, fn (\Throwable $e) => $e instanceof CouldNotPersistAggregate);
    }
}
