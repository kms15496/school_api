<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSession extends Model
{
    protected $fillable = ['exam_id', 'subject_id', 'date', 'start_time', 'end_time'];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

     public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
