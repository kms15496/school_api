<?php

namespace App\Repositories;

use App\Models\Parents;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\Student;

class ParentInfoRepository
{
    public function buildParentInfo(Parents $parent): array
    {
        $students = Student::where('parent_id', $parent->id)
            ->leftJoin(
                'student_academic_years',
                'students.id',
                '=',
                'student_academic_years.student_id'
            )
            ->where('student_academic_years.status', 1)
            ->select(
                'students.id',
                'students.name',
                'students.parent_id',
                'students.school_id'
            )
            ->get();

        $parent->setRelation('students', $students);

        // $studentIds = $students->pluck('id');
        $schoolIds = $students->pluck('school_id')->filter()->unique();

        $schools = $schoolIds->isEmpty()
            ? collect()
            : School::whereIn('id', $schoolIds)->get();

        $academicYears = $schoolIds->isEmpty()
            ? collect()
            : AcademicYear::whereIn('school_id', $schoolIds)->get();

        return [
            'parent' => $parent,
            'schools' => $schools,
            'academic_years' => $academicYears,
        ];
    }
}
