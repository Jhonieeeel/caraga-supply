<?php

namespace App\Actions\Guest;

use App\Models\Guest;

class CreateGuest {
    public function handle(array $data): Guest {
        return Guest::create($data);
    }
}
