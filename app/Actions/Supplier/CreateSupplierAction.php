<?php

namespace App\Actions\Supplier;

use App\Models\Supplier;

class CreateSupplierAction
{
    public function handle(array $data): Supplier
    {
        return Supplier::create($data);
    }
}
