<?php

namespace App\Domain\Gasu\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class RequisitionOpened extends ShouldBeStored
{
    public function __construct(
        public string $requisitionUuid,
        public int $userId,
        public ?int $requestedBy,
        public ?string $requestedDate,
        public ?string $purpose,
    ) {
    }
}
