<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassTimeTable extends Model
{
    protected $guarded = ['id'];

    protected $table = 'time_tables';

    public const DAYS = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function classSection()
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
