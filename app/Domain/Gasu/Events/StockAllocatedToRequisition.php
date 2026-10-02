<?php

namespace App\Domain\Gasu\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class StockAllocatedToRequisition extends ShouldBeStored
{
    public function __construct(
        public string $supplyUuid,
        public string $stockNumber,
        public string $requisitionUuid,
        public int $quantity,
    ) {
    }
}
