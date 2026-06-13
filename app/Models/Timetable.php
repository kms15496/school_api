<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    protected $table = 'timetables';

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
