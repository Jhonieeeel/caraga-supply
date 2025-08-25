<?php

namespace App\Actions\Stock;

use App\Models\Stock;

class CreateStockAction {
    public function handle(array $data): Stock {
        return Stock::create($data);
    }
}
