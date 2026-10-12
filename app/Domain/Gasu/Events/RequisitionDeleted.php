<?php

namespace App\Domain\Gasu\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class RequisitionDeleted extends ShouldBeStored
{
    public function __construct(
        public string $requisitionUuid,
        public bool $wasCompleted,
        public ?int $occurredBy = null,
    ) {
    }
}
