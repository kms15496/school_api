<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClassTimeTable as TimeTable;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function getList(Request $request)
    {
        $schoolId = $request->input('school_id');
        $academicYearId = $request->input('aay');
        $classId = $request->input('class_id');
        $sectionId = $request->input('section_id');
        $day = $request->input('day');

       

        if (!$schoolId || !$academicYearId || !$classId || !$sectionId) {
            return $this->apiResponse(false, 'Invalid request context', [], 200);
        }

        if (!$day) {
            return $this->apiResponse(false, 'Missing required parameter: day', [], 200);
        }

        $day = $this->normalizeDay($day);
        if (!$day) {
            return $this->apiResponse(false, 'Invalid day', [], 200);
        }

        $query = TimeTable::query()
            ->select([
                'id',
                'school_id',
                'academic_year_id',
                'school_class_id',
                'class_section_id',
                'subject_id',
                'day',
                'period',
                'start_time',
                'end_time',
            ])
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $academicYearId)
            ->where('school_class_id', $classId)
            ->where('class_section_id', $sectionId)
            ->where('day', $day)
            ->with([
                'subject' => function ($subjectQuery) {
                    $subjectQuery->select(['id', 'name', 'code', 'optional']);
                },
            ])
            ->orderBy('period')
            ->orderBy('start_time')
            ->orderBy('id');


        $timetables = $query->get();

        if ($timetables->isEmpty()) {
            return $this->apiResponse(false, 'Timetable not found for the selected day', [], 200);
        }

        return $this->apiResponse(true, 'Timetable fetched successfully', $timetables);
    }

    private function normalizeDay(string $day): ?string
    {
        $day = ucfirst(strtolower(trim($day)));

        if (in_array($day, TimeTable::DAYS, true)) {
            return $day;
        }

        return null;
    }
}
