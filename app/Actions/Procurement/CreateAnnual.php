<?php

namespace App\Actions\Procurement;

use App\Models\Procurement;

class CreateAnnual {
    public function handle(array $data): Procurement {
        return Procurement::create($data);
    }

}
