<?php

namespace App\Domain\Gasu\Aggregates;

use App\Domain\Gasu\Events\RequisitionAllocationRecorded;
use App\Domain\Gasu\Events\RequisitionCompleted;
use App\Domain\Gasu\Events\RequisitionDetailsUpdated;
use App\Domain\Gasu\Events\RequisitionOpened;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class RequisitionAggregate extends AggregateRoot
{
    protected ?int $userId = null;

    protected bool $completed = false;

    public function open(int $userId, ?int $requestedBy, ?string $requestedDate, ?string $purpose): static
    {
        $this->recordThat(new RequisitionOpened(
            requisitionUuid: $this->uuid(),
            userId: $userId,
            requestedBy: $requestedBy,
            requestedDate: $requestedDate,
            purpose: $purpose,
        ));

        return $this;
    }

    public function recordAllocation(string $supplyUuid, string $stockNumber, int $quantity): static
    {
        $this->recordThat(new RequisitionAllocationRecorded(
            requisitionUuid: $this->uuid(),
            supplyUuid: $supplyUuid,
            stockNumber: $stockNumber,
            quantity: $quantity,
        ));

        return $this;
    }

    /**
     * @param  array{ris?: ?string, approved_by?: ?int, issued_by?: ?int, received_by?: ?int, requested_date?: ?string, approved_date?: ?string, issued_date?: ?string, received_date?: ?string, purpose?: ?string}  $data
     */
    public function updateDetails(array $data, ?int $occurredBy = null): static
    {
        $this->recordThat(new RequisitionDetailsUpdated(
            requisitionUuid: $this->uuid(),
            ris: $data['ris'] ?? null,
            approvedBy: $data['approved_by'] ?? null,
            issuedBy: $data['issued_by'] ?? null,
            receivedBy: $data['received_by'] ?? null,
            requestedDate: $data['requested_date'] ?? null,
            approvedDate: $data['approved_date'] ?? null,
            issuedDate: $data['issued_date'] ?? null,
            receivedDate: $data['received_date'] ?? null,
            purpose: $data['purpose'] ?? null,
            occurredBy: $occurredBy,
        ));

        return $this;
    }

    /**
     * Guards internally against re-completing an already-completed requisition:
     * if this is called again (e.g. a later, unrelated edit re-saves the form),
     * it's a structural no-op rather than re-firing the completion event, which
     * is what permanently closes the duplicate-transaction-logging bug class.
     */
    public function complete(string $pdfPath, ?int $completedBy): static
    {
        if ($this->completed) {
            return $this;
        }

        $this->recordThat(new RequisitionCompleted(
            requisitionUuid: $this->uuid(),
            pdfPath: $pdfPath,
            completedBy: $completedBy,
        ));

        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    protected function applyRequisitionOpened(RequisitionOpened $event): void
    {
        $this->userId = $event->userId;
    }

    protected function applyRequisitionCompleted(RequisitionCompleted $event): void
    {
        $this->completed = true;
    }
}
