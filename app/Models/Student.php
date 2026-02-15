<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function academicYears()
    {
        return $this->hasMany(StudentAcademicYear::class, 'student_id')->where('status', true);
    }
}
