<?php

namespace App\Actions\Supplier;

use App\Models\Supplier;

class DeleteSupplierAction
{
    public function handle(Supplier $supplier): bool
    {
        return (bool) $supplier->delete();
    }
}
