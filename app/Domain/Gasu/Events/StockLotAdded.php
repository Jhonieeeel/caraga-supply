<?php

namespace App\Domain\Gasu\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class StockLotAdded extends ShouldBeStored
{
    public function __construct(
        public string $supplyUuid,
        public string $stockNumber,
        public ?string $barcode,
        public ?string $stockLocation,
        public float $price,
        public ?int $occurredBy = null,
    ) {
    }
}
