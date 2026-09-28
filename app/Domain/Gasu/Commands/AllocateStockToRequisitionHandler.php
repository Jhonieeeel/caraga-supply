<?php

namespace App\Domain\Gasu\Commands;

use App\Domain\Gasu\Aggregates\RequisitionAggregate;
use App\Domain\Gasu\Aggregates\SupplyAggregate;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Spatie\EventSourcing\AggregateRoots\Exceptions\CouldNotPersistAggregate;

class AllocateStockToRequisitionHandler
{
    /**
     * Allocates $quantity units of a supply (identified by the stock lot the
     * user anchored on) to a requisition, auto-spilling across the supply's
     * other lots if the anchor lot alone isn't enough. Retries on an
     * optimistic-concurrency conflict (another process changed the same
     * Supply's stock between our retrieve and persist), recomputing the
     * allocation against fresh quantities each attempt rather than
     * overwriting a concurrent change.
     */
    public function handle(string $supplyUuid, string $anchorStockNumber, int $quantity, string $requisitionUuid): void
    {
        retry(3, function () use ($supplyUuid, $anchorStockNumber, $quantity, $requisitionUuid) {
            $supply = SupplyAggregate::retrieve($supplyUuid);
            $allocations = $supply->allocate($anchorStockNumber, $quantity, $requisitionUuid);

            $requisition = RequisitionAggregate::retrieve($requisitionUuid);

            foreach ($allocations as $stockNumber => $allocatedQuantity) {
                $requisition->recordAllocation($supplyUuid, $stockNumber, $allocatedQuantity);
            }

            AggregateRoot::persistInTransaction($supply, $requisition);
        }, 50, fn (\Throwable $e) => $e instanceof CouldNotPersistAggregate);
    }
}
