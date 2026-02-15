<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\AttendanceRepository;
use Illuminate\Http\Request;

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
}
