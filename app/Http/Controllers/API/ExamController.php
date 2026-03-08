<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

    public function getList(Request $request)
    {
        $academicYearId = $request->input('aay');
        $classId = $request->input('class_id');
        if (!$academicYearId || !$classId) {
            return $this->apiResponse(false, 'Invalid request context', null, 400);
        }

        $exams = Exam::query()
            ->where('school_class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->select(['id', 'name', 'school_class_id', 'academic_year_id'])

            ->get();

        if ($exams->isEmpty()) {
            return $this->apiResponse(false, 'No exams found for the student\'s class and academic year', [], 404);
        }

        return $this->apiResponse(true, 'Exams fetched successfully', $exams);
    }

    public function getDetail(Request $request, Exam $exam)
    {
        $academicYearId = $request->input('aay');
        $classId = $request->input('class_id');
        if (!$academicYearId || !$classId) {
            return $this->apiResponse(false, 'Invalid request context', null, 400);
        }

        if ($exam->school_class_id != $classId || $exam->academic_year_id != $academicYearId) {
            return $this->apiResponse(false, 'Exam not found for the student\'s class and academic year', null, 404);
        }

        $exam->load(['sessions.subject']);

        return $this->apiResponse(true, 'Exam details fetched successfully', $exam);
    }

    public function getSessions(Request $request, Exam $exam)
    {
        $academicYearId = $request->input('aay');
        $classId = $request->input('class_id');
        if (!$academicYearId || !$classId) {
            return $this->apiResponse(false, 'Invalid request context', null, 400);
        }

        if ($exam->school_class_id != $classId || $exam->academic_year_id != $academicYearId) {
            return $this->apiResponse(false, 'Exam not found for the student\'s class and academic year', null, 404);
        }

        $sessions = $exam->sessions()
            ->select(['id', 'exam_id', 'subject_id', 'date', 'start_time', 'end_time'])
            ->with([
                'subject' => function ($subjectQuery) {
                    $subjectQuery->select(['id', 'name', 'code', 'optional']);
                },
            ])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        if ($sessions->isEmpty()) {
            return $this->apiResponse(false, 'No sessions found for this exam', [], 404);
        }

        return $this->apiResponse(true, 'Exam sessions fetched successfully', $sessions);
    }

    public function getReportCard(Request $request)
    {
        $academicYearId = $request->input('aay');
        $classId = $request->input('class_id');
        $studentId = $request->input('student_id');
        $examId = $request->input('exam_id');
        if (!$academicYearId || !$classId || !$studentId || !$examId) {
            return $this->apiResponse(false, 'Missing required parameters: exam_id', null, 400);
        }

        if (!Schema::hasTable('exam_session_marks')) {
            return $this->apiResponse(false, 'Report card data source not found', null, 500);
        }

        $markColumns = Schema::getColumnListing('exam_session_marks');
        $sessionForeignKey = null;
        if (in_array('exam_session_id', $markColumns, true)) {
            $sessionForeignKey = 'exam_session_id';
        } elseif (in_array('session_id', $markColumns, true)) {
            $sessionForeignKey = 'session_id';
        }

        if (!$sessionForeignKey || !in_array('student_id', $markColumns, true)) {
            return $this->apiResponse(false, 'Report card table is misconfigured', null, 500);
        }

        $rows = DB::table('exam_session_marks as esm')
            ->join('exam_sessions as es', "esm.{$sessionForeignKey}", '=', 'es.id')
            ->join('exams as e', 'es.exam_id', '=', 'e.id')
            ->join('subjects as s', 'es.subject_id', '=', 's.id')
            ->where('esm.student_id', $studentId)
            ->where('e.school_class_id', $classId)
            ->where('e.academic_year_id', $academicYearId)
            ->where('e.id', $examId)
            ->select([
                'esm.*',
                'e.id as exam_ref_id',
                'e.name as exam_name',
                'es.id as session_ref_id',
                'es.date as session_date',
                'es.start_time',
                'es.end_time',
                's.id as subject_ref_id',
                's.name as subject_name',
                's.code as subject_code',
                's.optional as subject_optional',
            ])
            ->orderBy('es.date')
            ->orderBy('es.start_time')
            ->get();

        if ($rows->isEmpty()) {
            return $this->apiResponse(false, 'Report card not found', [], 404);
        }

        $first = $rows->first();
        $report = [
            'exam_id' => $first->exam_ref_id,
            'exam_name' => $first->exam_name,
            'sessions' => $rows->map(function ($row) {
                $payload = (array) $row;
                unset(
                    $payload['exam_ref_id'],
                    $payload['exam_name'],
                    $payload['session_ref_id'],
                    $payload['session_date'],
                    $payload['start_time'],
                    $payload['end_time'],
                    $payload['subject_ref_id'],
                    $payload['subject_name'],
                    $payload['subject_code'],
                    $payload['subject_optional']
                );

                return [
                    'session_id' => $row->session_ref_id,
                    'date' => $row->session_date,
                    'start_time' => $row->start_time,
                    'end_time' => $row->end_time,
                    'subject' => [
                        'id' => $row->subject_ref_id,
                        'name' => $row->subject_name,
                        'code' => $row->subject_code,
                        'optional' => $row->subject_optional,
                    ],
                    'marks' => $payload,
                ];
            })->values(),
        ];

        return $this->apiResponse(true, 'Report card fetched successfully', $report);
    }
}
