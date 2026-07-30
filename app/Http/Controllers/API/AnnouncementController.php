<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\StudentAcademicYear;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AnnouncementController extends Controller
{
    public function getList(Request $request)
    {
        $validated = $request->validate([
            'school_id' => ['required', 'integer'],
            'aay' => ['required', 'integer'],
            'student_id' => ['required', 'integer'],
        ]);

    
        $studentClassId = StudentAcademicYear::query()
            ->where('student_id', $validated['student_id'])
            ->where('academic_year_id', $validated['aay'])
            ->where('school_id', $validated['school_id'])
            ->value('school_class_id');

        if (!$studentClassId) {
            return $this->apiResponse(
                false,
                'Student class not found for the academic year',
                [],
                200
            );
        }

        $announcements = Announcement::query()
            ->select($this->getAnnouncementColumns())
            ->where('school_id', $validated['school_id'])
            ->where('academic_year_id', $validated['aay'])
            ->whereJsonContains('class_id', (string) $studentClassId)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get()
            ->makeHidden([
                'media',
                'created_at',
                'updated_at',
            ]);

        if ($announcements->isEmpty()) {
            return $this->apiResponse(
                false,
                'No announcements found for the student class and academic year',
                [],
                200
            );
        }

        return $this->apiResponse(
            true,
            'Announcements fetched successfully',
            $announcements
        );
    }
    public function getDetail(Request $request, int $announcementId)
    {
        $schoolId = $request->input('school_id');
        $academicYearId = $request->input('aay');
        $studentId = $request->input('student_id');

        if (!$schoolId || !$academicYearId || !$studentId) {
            return $this->apiResponse(false, 'Invalid request context', null, 400);
        }

        if (!Schema::hasTable('announcements')) {
            return $this->apiResponse(false, 'Announcement data source not found', null, 500);
        }

        $studentGrade = $this->resolveStudentGrade($studentId, $academicYearId);
        if ($studentGrade === null) {
            return $this->apiResponse(false, 'Student grade not found for the academic year', null, 404);
        }

        $columns = $this->getAnnouncementColumns();

        $announcement = Announcement::query()
            ->select($columns)
            ->whereKey($announcementId)
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $academicYearId);

        $this->applyGradeFilter($announcement, $studentGrade);

        $announcement = $announcement->first();

        if (!$announcement) {
            return $this->apiResponse(false, 'Announcement not found for the student grade and academic year', null, 404);
        }

        $announcement->makeHidden(['media']);

        return $this->apiResponse(true, 'Announcement details fetched successfully', $announcement);
    }

    private function resolveStudentGrade(int|string $studentId, int|string $academicYearId): ?int
    {
        $studentAcademicYear = StudentAcademicYear::query()
            ->where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->first();

        if (!$studentAcademicYear) {
            return null;
        }

        foreach (['grade', 'grade_id', 'school_class_id', 'class_id'] as $column) {
            if (isset($studentAcademicYear->{$column}) && $studentAcademicYear->{$column} !== null) {
                return (int) $studentAcademicYear->{$column};
            }
        }

        return null;
    }

    private function applyGradeFilter(Builder $query, int $studentGrade): void
    {
        $gradeColumn = $this->resolveAnnouncementGradeColumn();

        if (!$gradeColumn) {
            return;
        }

        $query->where(function (Builder $gradeQuery) use ($gradeColumn, $studentGrade) {
            $gradeQuery->whereNull($gradeColumn)
                ->orWhere($gradeColumn, '[]')
                ->orWhereJsonContains($gradeColumn, $studentGrade)
                ->orWhereJsonContains($gradeColumn, (string) $studentGrade);
        });
    }

    private function resolveAnnouncementGradeColumn(): ?string
    {
        foreach (['grades', 'grade'] as $column) {
            if (Schema::hasColumn('announcements', $column)) {
                return $column;
            }
        }

        return null;
    }

    private function getAnnouncementColumns(): array
    {
        $available = Schema::getColumnListing('announcements');
        $preferred = [
            'id',
            'school_id',
            'academic_year_id',
            'date',
            'title',
            'body',
            'description',
            'grades',
            'grade',
            'created_at',
            'updated_at',
        ];

        return array_values(array_intersect($preferred, $available));
    }
}
