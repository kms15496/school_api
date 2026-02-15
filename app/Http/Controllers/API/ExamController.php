<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function getExamTimetable(Request $request)
    {
        $month = $request->integer('month');
        $year = $request->integer('year');

        if (!$month || !$year) {
            return $this->apiResponse(false, 'Missing required parameters: month and year', null, 400);
        }

        if ($month < 1 || $month > 12 || $year < 2000 || $year > 2100) {
            return $this->apiResponse(false, 'Invalid month or year', null, 422);
        }

        $academicYearId = $request->input('aay');
        $classId = $request->input('class_id');
        if (!$academicYearId || !$classId) {
            return $this->apiResponse(false, 'Invalid request context', null, 400);
        }

        $sessions = ExamSession::query()
            ->select(['id', 'exam_id', 'subject_id', 'date', 'start_time', 'end_time'])
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->whereHas('exam', function ($query) use ($classId, $academicYearId) {
                $query->where('school_class_id', $classId)
                    ->where('academic_year_id', $academicYearId);
            })
            ->with([
                'exam' => function ($examQuery) {
                    $examQuery->select(['id', 'name', 'school_class_id', 'academic_year_id']);
                },
                'subject' => function ($subjectQuery) {
                    $subjectQuery->select(['id', 'name', 'code', 'optional']);
                },
            ])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        if ($sessions->isEmpty()) {
            return $this->apiResponse(false, 'Exam timetable not found', [], 404);
        }

        $grouped = $sessions->groupBy('date')->map(function ($items, $date) {
            return [
                'date' => $date,
                'day' => Carbon::parse($date)->format('l'),
                'sessions' => $items->values(),
            ];
        })->values();

        return $this->apiResponse(true, 'Exam timetable fetched successfully', $grouped);
    }
}
