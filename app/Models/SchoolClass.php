<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    protected $table = 'school_classes';

    public function subjects()
    {
        return $this->hasMany(\App\Models\Subject::class, 'school_class_id');
    }
}
