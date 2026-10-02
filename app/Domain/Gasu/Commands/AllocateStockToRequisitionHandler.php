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
        $this->handleMany([[$supplyUuid, $anchorStockNumber, $quantity]], $requisitionUuid);
    }

    /**
     * All-or-nothing allocation of several lines to one requisition: every
     * line is allocated against in-memory aggregates first and everything is
     * persisted in a single transaction only if ALL lines succeed, so an
     * InsufficientStockException on the 2nd line never leaves the 1st line
     * allocated.
     *
     * @param  array<int, array{0: string, 1: string, 2: int}>  $lines  [supply_uuid, anchor_stock_number, quantity]
     */
    public function handleMany(array $lines, string $requisitionUuid): void
    {
        retry(3, function () use ($lines, $requisitionUuid) {
            $requisition = RequisitionAggregate::retrieve($requisitionUuid);

            /** @var array<string, SupplyAggregate> $supplies */
            $supplies = [];

            foreach ($lines as [$supplyUuid, $anchorStockNumber, $quantity]) {
                $supply = $supplies[$supplyUuid] ??= SupplyAggregate::retrieve($supplyUuid);

                $allocations = $supply->allocate($anchorStockNumber, (int) $quantity, $requisitionUuid);

                foreach ($allocations as $stockNumber => $allocatedQuantity) {
                    $requisition->recordAllocation($supplyUuid, (string) $stockNumber, $allocatedQuantity);
                }
            }

            AggregateRoot::persistInTransaction(...array_values($supplies), ...[$requisition]);
        }, 50, fn (\Throwable $e) => $e instanceof CouldNotPersistAggregate);
    }
}
