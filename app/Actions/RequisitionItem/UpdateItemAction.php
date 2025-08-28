<?php

namespace App\Actions\RequisitionItem;

use App\Models\RequisitionItem;

class UpdateItemAction
{
    public function handle(RequisitionItem $item, array $data): RequisitionItem
    {
        $item->update($data);

        return $item;
    }
}
