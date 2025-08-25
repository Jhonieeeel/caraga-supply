<?php

namespace App\Actions\Supply;

use App\Models\Supply;

class CreateSupplyAction {
    public function handle(array $data): Supply {
        return Supply::create($data);
    }
}