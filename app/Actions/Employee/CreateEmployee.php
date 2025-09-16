<?php

namespace App\Actions\Employee;

use App\Models\Employee;

class CreateEmployee
{
    public function handle(array $data): Employee
    {
        return Employee::create($data);
    }
}
