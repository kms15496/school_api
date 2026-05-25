<?php

namespace App\Repositories;

use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Models\StudentAcademicYear;
use App\Models\StudentClassChangeHistory;

class StudentRepository
{
    public function __construct(protected Student $model) {}

    public function getStudent(int $id)
    {
        $student = $this->model::query()
            ->withoutGlobalScopes()
            ->leftJoin('student_academic_years', 'student_academic_years.student_id', '=', 'students.id')
            ->leftJoin('school_classes', 'school_classes.id', '=', 'student_academic_years.school_class_id')
            ->leftJoin('class_sections', 'class_sections.id', '=', 'student_academic_years.section_id')
            ->leftJoin('academic_years', 'student_academic_years.academic_year_id', '=', 'academic_years.id')

            ->select([
                'students.id',
                'students.name',
                'student_academic_years.day_boarding',
                'student_academic_years.school_id',
                'student_academic_years.school_class_id',
                'student_academic_years.section_id',
                'student_academic_years.academic_year_id',
                'school_classes.name as class_name',
                'class_sections.name as section_name',
                'academic_years.start_year',
                'student_academic_years.ot_payment',
                'students.phone'
            ])
            ->where('student_academic_years.student_id', $id)
            ->first();

        return $student;
    }

    public function getStudents($id)
    {
        $students = $this->model::query()
            ->withoutGlobalScopes()
            ->leftJoin('student_academic_years', 'student_academic_years.student_id', '=', 'students.id')
            ->leftJoin('school_classes', 'school_classes.id', '=', 'student_academic_years.school_class_id')
            ->leftJoin('class_sections', 'class_sections.id', '=', 'student_academic_years.section_id')
            ->leftJoin('academic_years', 'student_academic_years.academic_year_id', '=', 'academic_years.id')

            ->select([
                'students.id',
                'students.name',
                'student_academic_years.day_boarding',
                'student_academic_years.school_id',
                'student_academic_years.school_class_id',
                'student_academic_years.section_id',
                'student_academic_years.academic_year_id',
                'school_classes.name as class_name',
                'class_sections.name as section_name',
                'academic_years.start_year',
                'student_academic_years.ot_payment'
            ])
            ->whereIn('student_academic_years.student_id', $id)
            ->get();

        return $students;
    }

    public function getStudentByAcademicYear(int $id, int $academicYearId)
    {
        $student = $this->model::query()
            ->withoutGlobalScopes()
            ->leftJoin('student_academic_years', 'student_academic_years.student_id', '=', 'students.id')
            ->leftJoin('school_classes', 'school_classes.id', '=', 'student_academic_years.school_class_id')
            ->leftJoin('class_sections', 'class_sections.id', '=', 'student_academic_years.section_id')
            ->leftJoin('academic_years', 'student_academic_years.academic_year_id', '=', 'academic_years.id')

            ->select([
                'students.id',
                'students.name',
                'student_academic_years.day_boarding',
                'student_academic_years.school_id',
                'student_academic_years.school_class_id',
                'student_academic_years.section_id',
                'student_academic_years.academic_year_id',
                'school_classes.name as class_name',
                'class_sections.name as section_name',
                'academic_years.start_year',
                'student_academic_years.ot_payment',
                'students.phone'
            ])
            ->where('student_academic_years.student_id', $id)
            ->where('student_academic_years.academic_year_id', $academicYearId)
            ->first();

        return $student;
    }

    public function changeClass($id, $data)
    {
        DB::beginTransaction();

        try {

            StudentAcademicYear::query()
                ->where('student_id', $id)
                ->where('school_class_id', $data['from_school_class_id'])
                ->where('section_id', $data['from_class_section_id'])
                ->where('academic_year_id', $data['academic_year_id'])
                ->delete();

            // 2. Create / update new academic year record
            StudentAcademicYear::updateOrCreate(
                [
                    'student_id'       => $id,
                    'academic_year_id' => $data['academic_year_id'],
                ],
                [
                    'school_id'        => auth()->user()->school_id,
                    'school_class_id'  => $data['school_class_id'],
                    'section_id'       => $data['section_id'],
                ]
            );

            // 3. Insert history log
            StudentClassChangeHistory::create([
                'student_id'            => $id,
                'academic_year_id'       => $data['academic_year_id'],
                'from_school_class_id'   => $data['from_school_class_id'],
                'from_class_section_id'  => $data['from_class_section_id'],
                'to_school_class_id'     => $data['school_class_id'],
                'to_class_section_id'    => $data['section_id'],
                'created_by'             => auth()->id(),
            ]);

            DB::commit();

            return redirect()
                ->route('student.index')
                ->with('success', 'Change Grade Success');
        } catch (Throwable $e) {

            DB::rollBack();

            // optional: log for debugging
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Failed to change class. Please try again.');
        }
    }
}
