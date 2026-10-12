<?php

namespace App\Actions\Requisition;

use App\Domain\Gasu\Aggregates\RequisitionAggregate;
use App\Domain\Gasu\Aggregates\SupplyAggregate;
use App\Models\Requisition;
use Illuminate\Support\Facades\Auth;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Spatie\EventSourcing\AggregateRoots\Exceptions\CouldNotPersistAggregate;

class DeleteRequisitionAction
{
    /**
     * Not yet issued (not completed): every unit still allocated to the
     * requisition is released back to its stock lot through the
     * SupplyAggregate(s) before the requisition is deleted, all in one
     * transaction.
     *
     * Already issued (completed): the stock physically left the warehouse
     * and its RIS transactions were logged, so nothing is returned; only the
     * requisition record is removed (RequisitionPolicy::delete only lets
     * approvers do this).
     */
    public function handle(Requisition $requisition): void
    {
        if (! $requisition->uuid) {
            // Pre-event-sourcing row that was never backfilled: there is no
            // event history to release against.
            $requisition->delete();

            return;
        }

        retry(3, function () use ($requisition) {
            $aggregate = RequisitionAggregate::retrieve($requisition->uuid);
            $supplies = [];

            if (! $aggregate->isCompleted()) {
                foreach ($aggregate->allocatedSupplyUuids() as $supplyUuid) {
                    $supply = SupplyAggregate::retrieve($supplyUuid);

                    foreach ($supply->releaseAllocation($requisition->uuid, null, null, Auth::id()) as $lot => $released) {
                        $aggregate->recordAllocationRelease($supplyUuid, (string) $lot, $released);
                    }

                    $supplies[] = $supply;
                }
            }

            $aggregate->delete(Auth::id());

            AggregateRoot::persistInTransaction(...[...$supplies, $aggregate]);
        }, 50, fn (\Throwable $e) => $e instanceof CouldNotPersistAggregate);
    }
}
