<?php

namespace App\Actions\Supply;

use App\Models\Supply;

class EditSupplyAction {
    public function handle(Supply $supply, array $data): Supply {
        $supply->update($data);
        return $supply;
    }
}