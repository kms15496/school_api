<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\AttendanceRepository;
use Illuminate\Http\Request;
use App\Models\SchoolClass;
class StudentController extends Controller
{
    public function __construct(
        private readonly AttendanceRepository $attendanceRepository
    ) {
    }

    public function getAttendance()
    {
        $month = request()->integer('month');
        $year = request()->integer('year');
        $studentId = request()->input('student_id');
        $academicYearId = request()->input('aay');

        $attendance = $this->attendanceRepository->getAttendanceForMonth(
            $studentId,
            $academicYearId,
            $month,
            $year
        );
        $attendance->makeHidden(['created_at', 'updated_at', 'leave_reason']);

        $calendarData = $this->attendanceRepository->buildCalendarAttendance($attendance, $month, $year);

        return $this->apiResponse(true, 'Student attendance fetched successfully', $calendarData);
    }

    public function getSubjects(Request $request)
    {

        $studentId = request()->input('student_id');
        $academicYearId = request()->input('aay');
        $schoolId = request()->input('school_id');
        $classId = request()->input('class_id');

        $subjects = SchoolClass::query()
            ->select('id', 'name')
            ->where('school_id', $schoolId)
            ->with([
                'subjects' => function ($q) {
                    $q->select('id', 'name', 'school_class_id') // include order column
                        ->orderBy('order')   // ✅ sort by order field
                        ->orderBy('name');   // tie-break
                }
            ])
            ->when($classId, fn($q) => $q->where('id', $classId))
            ->first();


        return $this->apiResponse(true, 'Student subjects fetched successfully', $subjects);
    }
}
