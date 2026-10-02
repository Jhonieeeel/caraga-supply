<?php

namespace App\Domain\Gasu\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class SupplyRenamed extends ShouldBeStored
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
