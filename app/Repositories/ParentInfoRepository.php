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
            ->select('id', 'name', 'parent_id', 'school_id')
            ->get();

        $parent->setRelation('students', $students);

        $studentIds = $students->pluck('id');
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
