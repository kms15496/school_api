<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamMark extends Model
{
    protected $table = 'exam_session_marks';

    protected $guarded = ['id'];
}
