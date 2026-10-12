<?php

namespace App\Domain\Gasu\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class RequisitionAllocationRecorded extends ShouldBeStored
{
    public function __construct(
        public string $requisitionUuid,
        public string $supplyUuid,
        public string $stockNumber,
        public int $quantity,
    ) {
    }
}
