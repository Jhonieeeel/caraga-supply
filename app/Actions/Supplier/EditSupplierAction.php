<?php

namespace App\Actions\Supplier;

use App\Models\Supplier;

class EditSupplierAction
{
    public function handle(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($data);

        return $supplier;
    }
}
