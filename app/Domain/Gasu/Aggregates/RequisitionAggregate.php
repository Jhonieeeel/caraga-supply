<?php

namespace App\Domain\Gasu\Aggregates;

use App\Domain\Gasu\Events\RequisitionAllocationRecorded;
use App\Domain\Gasu\Events\RequisitionAllocationReleased;
use App\Domain\Gasu\Events\RequisitionCompleted;
use App\Domain\Gasu\Events\RequisitionDeleted;
use App\Domain\Gasu\Events\RequisitionDetailsUpdated;
use App\Domain\Gasu\Events\RequisitionOpened;
use App\Domain\Gasu\Exceptions\RequisitionLockedException;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class RequisitionAggregate extends AggregateRoot
{
    protected ?int $userId = null;

    protected bool $completed = false;

    protected bool $deleted = false;

    /**
     * Units currently allocated to this requisition.
     *
     * @var array<string, array<string, int>> supply_uuid => [stock_number => quantity]
     */
    protected array $allocations = [];

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
        $this->guardItemsCanChange();

        $this->recordThat(new RequisitionAllocationRecorded(
            requisitionUuid: $this->uuid(),
            supplyUuid: $supplyUuid,
            stockNumber: $stockNumber,
            quantity: $quantity,
        ));

        return $this;
    }

    public function recordAllocationRelease(string $supplyUuid, string $stockNumber, int $quantity): static
    {
        $this->guardItemsCanChange();

        $this->recordThat(new RequisitionAllocationReleased(
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

    /**
     * A not-yet-issued requisition must have its allocations released (see
     * DeleteRequisitionAction) before this is recorded. A completed one has
     * physically been issued, so its stock is never returned.
     */
    public function delete(?int $occurredBy = null): static
    {
        if ($this->deleted) {
            throw RequisitionLockedException::deleted($this->uuid());
        }

        $this->recordThat(new RequisitionDeleted(
            requisitionUuid: $this->uuid(),
            wasCompleted: $this->completed,
            occurredBy: $occurredBy,
        ));

        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    /**
     * @return array<int, string>
     */
    public function allocatedSupplyUuids(): array
    {
        return array_keys(array_filter($this->allocations));
    }

    protected function guardItemsCanChange(): void
    {
        if ($this->deleted) {
            throw RequisitionLockedException::deleted($this->uuid());
        }

        if ($this->completed) {
            throw RequisitionLockedException::completed($this->uuid());
        }
    }

    protected function applyRequisitionOpened(RequisitionOpened $event): void
    {
        $this->userId = $event->userId;
    }

    protected function applyRequisitionAllocationRecorded(RequisitionAllocationRecorded $event): void
    {
        $this->allocations[$event->supplyUuid][$event->stockNumber] =
            ($this->allocations[$event->supplyUuid][$event->stockNumber] ?? 0) + $event->quantity;
    }

    protected function applyRequisitionAllocationReleased(RequisitionAllocationReleased $event): void
    {
        $remaining = ($this->allocations[$event->supplyUuid][$event->stockNumber] ?? 0) - $event->quantity;

        if ($remaining > 0) {
            $this->allocations[$event->supplyUuid][$event->stockNumber] = $remaining;
        } else {
            unset($this->allocations[$event->supplyUuid][$event->stockNumber]);
        }
    }

    protected function applyRequisitionCompleted(RequisitionCompleted $event): void
    {
        $this->completed = true;
    }

    protected function applyRequisitionDeleted(RequisitionDeleted $event): void
    {
        $this->deleted = true;
    }
}
