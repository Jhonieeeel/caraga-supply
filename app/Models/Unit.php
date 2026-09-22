<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'name',
        'section_id',
        'description',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
