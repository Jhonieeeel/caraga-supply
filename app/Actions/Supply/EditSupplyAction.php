<?php

namespace App\Actions\Supply;

use App\Domain\Gasu\Aggregates\SupplyAggregate;
use App\Models\Supply;
use Illuminate\Support\Facades\Auth;

class EditSupplyAction
{
    public function handle(Supply $supply, array $data): Supply
    {
        SupplyAggregate::retrieve($supply->uuid)
            ->rename($data['name'], $data['category'] ?? null, $data['unit'] ?? null, Auth::id())
            ->persist();

        return $supply->fresh();
    }
}
