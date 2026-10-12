<?php

namespace App\Domain\Gasu\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class RequisitionDetailsUpdated extends ShouldBeStored
{
    public function __construct(
        public string $requisitionUuid,
        public ?string $ris,
        public ?int $approvedBy,
        public ?int $issuedBy,
        public ?int $receivedBy,
        public ?string $requestedDate,
        public ?string $approvedDate,
        public ?string $issuedDate,
        public ?string $receivedDate,
        public ?string $purpose,
        public ?int $occurredBy = null,
    ) {
    }
}
