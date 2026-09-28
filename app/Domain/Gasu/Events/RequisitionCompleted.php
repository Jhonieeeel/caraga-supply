<?php

namespace App\Domain\Gasu\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class RequisitionCompleted extends ShouldBeStored
{
    public function __construct(
        public string $requisitionUuid,
        public string $pdfPath,
        public ?int $completedBy,
    ) {
    }
}
