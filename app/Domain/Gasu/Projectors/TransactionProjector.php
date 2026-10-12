<?php

namespace App\Domain\Gasu\Projectors;

use App\Domain\Gasu\Events\RequisitionCompleted;
use App\Domain\Gasu\Events\StockReceived;
use App\Models\Requisition;
use App\Models\Stock;
use App\Models\Supply;
use App\Models\Transaction;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class TransactionProjector extends Projector
{
    public function onStockReceived(StockReceived $event): void
    {
        $supply = Supply::where('uuid', $event->supplyUuid)->firstOrFail();

        $stock = Stock::where('supply_id', $supply->id)
            ->where('stock_number', $event->stockNumber)
            ->firstOrFail();

        Transaction::create([
            'stock_id' => $stock->id,
            'quantity' => $event->quantity,
            'current_quantity' => $stock->quantity,
            'type_of_transaction' => 'PO',
        ]);
    }

    /**
     * Fires exactly once per requisition — RequisitionAggregate::complete()
     * guards against re-completion, so this event only ever occurs once for
     * a given requisition, permanently closing the duplicate-transaction bug.
     */
    public function onRequisitionCompleted(RequisitionCompleted $event): void
    {
        $requisition = Requisition::where('uuid', $event->requisitionUuid)
            ->with('items.stock')
            ->firstOrFail();

        foreach ($requisition->items as $item) {
            Transaction::create([
                'requisition_id' => $requisition->id,
                'stock_id' => $item->stock_id,
                'quantity' => $item->requested_qty,
                'current_quantity' => $item->stock->quantity,
                'type_of_transaction' => 'RIS',
            ]);
        }
    }
}
