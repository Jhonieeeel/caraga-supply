<?php

namespace App\Actions\Supply;

use App\Domain\Gasu\Aggregates\SupplyAggregate;
use App\Models\Supply;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreateSupplyAction
{
    public function handle(array $data): Supply
    {
        $uuid = (string) Str::uuid();

        SupplyAggregate::retrieve($uuid)
            ->createSupply($data['name'], $data['category'] ?? null, $data['unit'] ?? null, Auth::id())
            ->persist();

        return Supply::where('uuid', $uuid)->firstOrFail();
    }
}
