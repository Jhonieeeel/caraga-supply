<?php

namespace App\Domain\Gasu\Projectors;

use App\Domain\Gasu\Events\RequisitionCompleted;
use App\Domain\Gasu\Events\RequisitionDeleted;
use App\Domain\Gasu\Events\RequisitionDetailsUpdated;
use App\Domain\Gasu\Events\RequisitionOpened;
use App\Models\Requisition;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class RequisitionProjector extends Projector
{
    public function onRequisitionOpened(RequisitionOpened $event): void
    {
        Requisition::create([
            'uuid' => $event->requisitionUuid,
            'user_id' => $event->userId,
            'requested_by' => $event->requestedBy,
            'requested_date' => $event->requestedDate,
            'purpose' => $event->purpose,
            'status' => 'pending',
            'completed' => false,
        ]);
    }

    public function onRequisitionDetailsUpdated(RequisitionDetailsUpdated $event): void
    {
        Requisition::where('uuid', $event->requisitionUuid)->update([
            'ris' => $event->ris,
            'approved_by' => $event->approvedBy,
            'issued_by' => $event->issuedBy,
            'received_by' => $event->receivedBy,
            'requested_date' => $event->requestedDate,
            'approved_date' => $event->approvedDate,
            'issued_date' => $event->issuedDate,
            'received_date' => $event->receivedDate,
            'purpose' => $event->purpose,
        ]);
    }

    public function onRequisitionDeleted(RequisitionDeleted $event): void
    {
        $requisition = Requisition::where('uuid', $event->requisitionUuid)->first();

        if (! $requisition) {
            return;
        }

        $requisition->items()->delete();
        $requisition->delete();
    }

    public function onRequisitionCompleted(RequisitionCompleted $event): void
    {
        Requisition::where('uuid', $event->requisitionUuid)->update([
            'pdf' => $event->pdfPath,
            'status' => 'completed',
            'completed' => true,
        ]);
    }
}
