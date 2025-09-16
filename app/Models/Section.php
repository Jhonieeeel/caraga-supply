<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
