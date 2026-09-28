<?php

namespace App\Domain\Gasu\Projectors;

use App\Domain\Gasu\Events\StockAllocatedToRequisition;
use App\Domain\Gasu\Events\StockLotAdded;
use App\Domain\Gasu\Events\StockLotDetailsUpdated;
use App\Domain\Gasu\Events\StockReceived;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Stock;
use App\Models\Supply;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class StockProjector extends Projector
{
    /**
     * Runs before other projectors (e.g. TransactionProjector) that read a
     * Stock's post-mutation quantity for the same event.
     */
    protected int $weight = -10;

    public function onStockLotAdded(StockLotAdded $event): void
    {
        $supply = Supply::where('uuid', $event->supplyUuid)->firstOrFail();

        Stock::create([
            'supply_id' => $supply->id,
            'stock_number' => $event->stockNumber,
            'barcode' => $event->barcode,
            'stock_location' => $event->stockLocation,
            'price' => $event->price,
            'quantity' => 0,
            'initial_quantity' => 0,
        ]);
    }

    public function onStockLotDetailsUpdated(StockLotDetailsUpdated $event): void
    {
        $supply = Supply::where('uuid', $event->supplyUuid)->firstOrFail();

        Stock::where('supply_id', $supply->id)
            ->where('stock_number', $event->stockNumber)
            ->update([
                'barcode' => $event->barcode,
                'stock_location' => $event->stockLocation,
                'price' => $event->price,
            ]);
    }

    public function onStockReceived(StockReceived $event): void
    {
        $supply = Supply::where('uuid', $event->supplyUuid)->firstOrFail();

        $stock = Stock::where('supply_id', $supply->id)
            ->where('stock_number', $event->stockNumber)
            ->firstOrFail();

        $stock->increment('quantity', $event->quantity);
        $stock->increment('initial_quantity', $event->quantity);
    }

    public function onStockAllocatedToRequisition(StockAllocatedToRequisition $event): void
    {
        $supply = Supply::where('uuid', $event->supplyUuid)->firstOrFail();

        $stock = Stock::where('supply_id', $supply->id)
            ->where('stock_number', $event->stockNumber)
            ->firstOrFail();

        $stock->decrement('quantity', $event->quantity);

        $requisition = Requisition::where('uuid', $event->requisitionUuid)->firstOrFail();

        $existingItem = RequisitionItem::where('requisition_id', $requisition->id)
            ->where('stock_id', $stock->id)
            ->first();

        RequisitionItem::updateOrCreate(
            ['requisition_id' => $requisition->id, 'stock_id' => $stock->id],
            ['requested_qty' => ($existingItem->requested_qty ?? 0) + $event->quantity],
        );
    }
}
