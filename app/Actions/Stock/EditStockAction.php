<?php

namespace App\Actions\Stock;

use App\Models\Stock;

class EditStockAction {
    public function handle(Stock $stock, array $data) {
        $stock->update($data);
        return $stock;
    }
}
