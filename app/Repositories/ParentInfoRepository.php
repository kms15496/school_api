<?php

namespace App\Repositories;

use App\Models\Parents;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\Student;
use App\Models\Setting;

class ParentInfoRepository
{
    // public function buildParentInfo(Parents $parent): array
    // {
    //     $students = Student::where('parent_id', $parent->id)
    //         ->leftJoin(
    //             'student_academic_years',
    //             'students.id',
    //             '=',
    //             'student_academic_years.student_id'
    //         )
    //         ->where('student_academic_years.status', 1)
    //         ->select(
    //             'students.id',
    //             'students.name',
    //             'students.parent_id',
    //             'students.school_id'
    //         )
    //         ->get();

    //     $parent->setRelation('students', $students);

    //     // $studentIds = $students->pluck('id');
    //     $schoolIds = $students->pluck('school_id')->filter()->unique();

    //     $settings = Setting::select('allow_academic_year')->whereIn('school_id', $schoolIds)->get();

    //     $schools = $schoolIds->isEmpty()
    //         ? collect()
    //         : School::whereIn('id', $schoolIds)->get();

    //     $academicYears = $schoolIds->isEmpty()
    //         ? collect()
    //         : AcademicYear::whereIn('school_id', $schoolIds)->get();

    //     return [
    //         'parent' => $parent,
    //         'schools' => $schools,
    //         'academic_years' => $academicYears,
    //     ];
    // }

    public function buildParentInfo(Parents $parent): array
    {
        $students = Student::query()
            ->where('students.parent_id', $parent->id)
            ->join(
                'student_academic_years',
                'students.id',
                '=',
                'student_academic_years.student_id'
            )
            ->join(
                'settings',
                'students.school_id',
                '=',
                'settings.school_id'
            )
            ->where('student_academic_years.status', 1)
            ->whereColumn(
                'student_academic_years.academic_year_id',
                'settings.allow_academic_year'
            )
            ->select(
                'students.id',
                'students.name',
                'students.parent_id',
                'students.school_id'
            )
            ->distinct()
            ->get();

        $parent->setRelation('students', $students);

        $schoolIds = $students
            ->pluck('school_id')
            ->filter()
            ->unique()
            ->values();

        $settings = $schoolIds->isEmpty()
            ? collect()
            : Setting::whereIn('school_id', $schoolIds)
                ->select('school_id', 'allow_academic_year')
                ->get();

        $schools = $schoolIds->isEmpty()
            ? collect()
            : School::whereIn('id', $schoolIds)->get();

        $academicYears = $schoolIds->isEmpty()
            ? collect()
            : AcademicYear::whereIn('school_id', $schoolIds)
                ->whereIn(
                    'id',
                    $settings->pluck('allow_academic_year')->filter()->unique()
                )
                ->get();

        return [
            'parent' => $parent,
            'schools' => $schools,
            'academic_years' => $academicYears,
        ];
    }
}
