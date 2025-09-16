<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
