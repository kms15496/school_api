<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAcademicYear extends Model
{
    protected $table = 'student_academic_years';

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
