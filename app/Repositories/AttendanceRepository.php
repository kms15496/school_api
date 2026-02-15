<?php

namespace App\Repositories;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceRepository
{
    public function buildCalendarAttendance(Collection $attendance, int $month, int $year): array
    {
        $attendanceByDate = $attendance->keyBy(function ($record) {
            return Carbon::parse($record->date)->toDateString();
        });

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $calendar = [];
        $presentDays = 0;
        $absentDays = 0;
        $leaveDays = 0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            $dateString = $date->toDateString();

            if ($date->isWeekend()) {
                $status = 'school_off';
            } elseif ($attendanceByDate->has($dateString)) {
                $status = $attendanceByDate->get($dateString)->status;
            } else {
                $status = 'unknown';
            }

            if ($status === 'present') {
                $presentDays++;
            } elseif ($status === 'absent') {
                $absentDays++;
            } elseif ($status === 'leave') {
                $leaveDays++;
            }

            $calendar[] = [
                'date' => $dateString,
                'status' => $status,
            ];
        }

        $attendancePercentage = $daysInMonth === 0
            ? 0
            : round(($presentDays / $daysInMonth) * 100);

        return [
            'attendance' => $calendar,
            'meta' => [
                'total_days' => $daysInMonth,
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'leave_days' => $leaveDays,
                'total_calendar_days' => $daysInMonth,
                'attendance_percentage' => $attendancePercentage,
            ],
        ];
    }

    public function getAttendanceForMonth(int $studentId, int $academicYearId, int $month, int $year): Collection
    {
        return Attendance::query()
            ->where('student_id', $studentId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('academic_year_id', $academicYearId)
            ->get();
    }

}
