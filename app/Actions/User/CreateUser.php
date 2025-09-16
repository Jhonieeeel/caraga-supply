<?php

namespace App\Actions\User;

use App\Models\User;

class CreateUser
{
    public function handle(array $data)
    {
        return User::create($data);
    }
}
