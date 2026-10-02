<?php

namespace App\Domain\Gasu\Projectors;

use App\Domain\Gasu\Events\SupplyCreated;
use App\Domain\Gasu\Events\SupplyRenamed;
use App\Models\Supply;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class SupplyProjector extends Projector
{
    public function onSupplyCreated(SupplyCreated $event): void
    {
        Supply::create([
            'uuid' => $event->supplyUuid,
            'name' => $event->name,
            'category' => $event->category,
            'unit' => $event->unit,
        ]);
    }

    public function onSupplyRenamed(SupplyRenamed $event): void
    {
        Supply::where('uuid', $event->supplyUuid)->update([
            'name' => $event->name,
            'category' => $event->category,
            'unit' => $event->unit,
        ]);
    }
}
