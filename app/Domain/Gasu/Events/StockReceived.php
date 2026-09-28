<?php

namespace App\Domain\Gasu\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class StockReceived extends ShouldBeStored
{
    public function __construct(
        public string $supplyUuid,
        public string $stockNumber,
        public int $quantity,
        public ?int $occurredBy = null,
    ) {
    }
}
