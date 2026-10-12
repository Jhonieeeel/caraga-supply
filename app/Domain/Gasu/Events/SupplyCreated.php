<?php

namespace App\Domain\Gasu\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class SupplyCreated extends ShouldBeStored
{
    public function __construct(
        public string $supplyUuid,
        public string $name,
        public ?string $category,
        public ?string $unit,
        public ?int $occurredBy = null,
    ) {
    }
}
