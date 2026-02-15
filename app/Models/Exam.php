<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = ['name', 'school_class_id','academic_year_id'];

    public function sessions()
    {
        return $this->hasMany(ExamSession::class);
    }
    public function subjects()
    {
        return $this->hasManyThrough(
            Subject::class,
            ExamSession::class,
            'exam_id',     // FK on exam_sessions
            'id',          // FK on subjects (local key in exam_sessions.subject_id)
            'id',          // PK on exams
            'subject_id'   // FK on exam_sessions
        );
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

     public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
