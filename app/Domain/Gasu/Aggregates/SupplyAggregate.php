<?php

namespace App\Domain\Gasu\Aggregates;

use App\Domain\Gasu\Events\StockAllocatedToRequisition;
use App\Domain\Gasu\Events\StockLotAdded;
use App\Domain\Gasu\Events\StockLotDetailsUpdated;
use App\Domain\Gasu\Events\StockReceived;
use App\Domain\Gasu\Events\SupplyCreated;
use App\Domain\Gasu\Events\SupplyRenamed;
use App\Domain\Gasu\Exceptions\InsufficientStockException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class SupplyAggregate extends AggregateRoot
{
    protected ?string $name = null;

    protected ?string $category = null;

    protected ?string $unit = null;

    /**
     * Keyed by stock_number.
     *
     * @var array<string, array{quantity: int, barcode: ?string, stockLocation: ?string, price: float, createdAt: string}>
     */
    protected array $lots = [];

    public function createSupply(string $name, ?string $category, ?string $unit, ?int $occurredBy = null): static
    {
        $this->recordThat(new SupplyCreated(
            supplyUuid: $this->uuid(),
            name: $name,
            category: $category,
            unit: $unit,
            occurredBy: $occurredBy,
        ));

        return $this;
    }

    public function rename(string $name, ?string $category, ?string $unit, ?int $occurredBy = null): static
    {
        $this->recordThat(new SupplyRenamed(
            supplyUuid: $this->uuid(),
            name: $name,
            category: $category,
            unit: $unit,
            occurredBy: $occurredBy,
        ));

        return $this;
    }

    public function addLot(string $stockNumber, ?string $barcode, ?string $stockLocation, float $price, int $initialQuantity = 0, ?int $occurredBy = null): static
    {
        $this->recordThat(new StockLotAdded(
            supplyUuid: $this->uuid(),
            stockNumber: $stockNumber,
            barcode: $barcode,
            stockLocation: $stockLocation,
            price: $price,
            occurredBy: $occurredBy,
        ));

        if ($initialQuantity > 0) {
            $this->receiveStock($stockNumber, $initialQuantity, $occurredBy);
        }

        return $this;
    }

    public function updateLotDetails(string $stockNumber, ?string $barcode, ?string $stockLocation, float $price, ?int $occurredBy = null): static
    {
        $this->recordThat(new StockLotDetailsUpdated(
            supplyUuid: $this->uuid(),
            stockNumber: $stockNumber,
            barcode: $barcode,
            stockLocation: $stockLocation,
            price: $price,
            occurredBy: $occurredBy,
        ));

        return $this;
    }

    public function receiveStock(string $stockNumber, int $quantity, ?int $occurredBy = null): static
    {
        $this->recordThat(new StockReceived(
            supplyUuid: $this->uuid(),
            stockNumber: $stockNumber,
            quantity: $quantity,
            occurredBy: $occurredBy,
        ));

        return $this;
    }

    /**
     * Allocate $quantity units against this supply, drawing first from the
     * anchor lot the user picked, then spilling the remainder across the
     * supply's other lots oldest-created-first (FIFO), until satisfied.
     *
     * Throws (persisting nothing) if the supply's total available stock,
     * across all lots, is insufficient for the requested quantity.
     *
     * @return array<string, int> stock_number => quantity allocated from that lot
     */
    public function allocate(string $anchorStockNumber, int $quantity, string $requisitionUuid): array
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Allocation quantity must be positive.');
        }

        $totalAvailable = array_sum(array_column($this->lots, 'quantity'));

        if ($totalAvailable < $quantity) {
            throw InsufficientStockException::forSupply($this->uuid(), $quantity, $totalAvailable);
        }

        $remaining = $quantity;
        $allocations = [];

        if (isset($this->lots[$anchorStockNumber]) && $this->lots[$anchorStockNumber]['quantity'] > 0) {
            $take = min($remaining, $this->lots[$anchorStockNumber]['quantity']);
            $allocations[$anchorStockNumber] = $take;
            $remaining -= $take;
        }

        if ($remaining > 0) {
            $otherLotsOldestFirst = Collection::make($this->lots)
                ->except($anchorStockNumber)
                ->filter(fn (array $lot) => $lot['quantity'] > 0)
                ->sortBy('createdAt');

            foreach ($otherLotsOldestFirst as $stockNumber => $lot) {
                if ($remaining <= 0) {
                    break;
                }

                $take = min($remaining, $lot['quantity']);
                $allocations[$stockNumber] = ($allocations[$stockNumber] ?? 0) + $take;
                $remaining -= $take;
            }
        }

        foreach ($allocations as $stockNumber => $allocatedQuantity) {
            $this->recordThat(new StockAllocatedToRequisition(
                supplyUuid: $this->uuid(),
                stockNumber: $stockNumber,
                requisitionUuid: $requisitionUuid,
                quantity: $allocatedQuantity,
            ));
        }

        return $allocations;
    }

    protected function applySupplyCreated(SupplyCreated $event): void
    {
        $this->name = $event->name;
        $this->category = $event->category;
        $this->unit = $event->unit;
    }

    protected function applySupplyRenamed(SupplyRenamed $event): void
    {
        $this->name = $event->name;
        $this->category = $event->category;
        $this->unit = $event->unit;
    }

    protected function applyStockLotAdded(StockLotAdded $event): void
    {
        $this->lots[$event->stockNumber] = [
            'quantity' => 0,
            'barcode' => $event->barcode,
            'stockLocation' => $event->stockLocation,
            'price' => $event->price,
            'createdAt' => ($event->createdAt() ?? CarbonImmutable::now())->toIso8601String(),
        ];
    }

    protected function applyStockLotDetailsUpdated(StockLotDetailsUpdated $event): void
    {
        if (! isset($this->lots[$event->stockNumber])) {
            return;
        }

        $this->lots[$event->stockNumber]['barcode'] = $event->barcode;
        $this->lots[$event->stockNumber]['stockLocation'] = $event->stockLocation;
        $this->lots[$event->stockNumber]['price'] = $event->price;
    }

    protected function applyStockReceived(StockReceived $event): void
    {
        $this->lots[$event->stockNumber]['quantity'] = ($this->lots[$event->stockNumber]['quantity'] ?? 0) + $event->quantity;
    }

    protected function applyStockAllocatedToRequisition(StockAllocatedToRequisition $event): void
    {
        $this->lots[$event->stockNumber]['quantity'] -= $event->quantity;
    }
}
