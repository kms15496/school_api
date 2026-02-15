<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $table = 'academic_years';

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
