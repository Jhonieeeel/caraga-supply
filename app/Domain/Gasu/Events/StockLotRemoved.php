<?php

namespace App\Domain\Gasu\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

/**
 * Only ever recorded for a lot that was never allocated to any requisition
 * (see SupplyAggregate::removeLot()); $quantityWrittenOff is whatever
 * received-but-never-issued quantity the lot still held.
 */
class StockLotRemoved extends ShouldBeStored
{
    public function __construct(
        public string $supplyUuid,
        public string $stockNumber,
        public int $quantityWrittenOff,
        public ?int $occurredBy = null,
    ) {
    }
}
