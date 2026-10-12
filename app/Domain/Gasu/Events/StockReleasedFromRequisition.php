<?php

namespace App\Domain\Gasu\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

/**
 * Supply-stream counterpart of StockAllocatedToRequisition: units that were
 * allocated (reserved) to a not-yet-issued requisition go back on the lot,
 * e.g. when the requisition is deleted, an item is removed, or an item's
 * requested quantity is lowered.
 */
class StockReleasedFromRequisition extends ShouldBeStored
{
    public function __construct(
        public string $supplyUuid,
        public string $stockNumber,
        public string $requisitionUuid,
        public int $quantity,
        public ?int $occurredBy = null,
    ) {
    }
}
