<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LessonPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class LessonPlanController extends Controller
{
    public function getList(Request $request)
    {
        $schoolId = $request->input('school_id');
        $academicYearId = $request->input('aay');
        $classId = $request->input('class_id');
        $sectionId = $request->input('section_id');
        $subjectId = $request->input('subject_id');

        if (!$schoolId || !$academicYearId) {
            return $this->apiResponse(false, 'Invalid request context', null, 400);
        }


        try {
            $targetDate = $request->filled('date')
                ? Carbon::parse($request->input('date'))->toDateString()
                : now()->toDateString();
        } catch (\Throwable $exception) {
            return $this->apiResponse(false, 'Invalid date format', null, 422);
        }

        // dd($request->all());
        $availableColumns = Schema::getColumnListing('lesson_plans');

        $lessonPlans = LessonPlan::query()
        ->where('subject_id', $subjectId)
            ->select($this->getLessonPlanColumns($availableColumns));

        $this->applyExactFilter($lessonPlans, $availableColumns, ['school_id'], $schoolId);
        $this->applyExactFilter($lessonPlans, $availableColumns, ['academic_year_id', 'aay'], $academicYearId);
        $this->applyExactFilter($lessonPlans, $availableColumns, ['school_class_id', 'class_id', 'grade_id', 'grade'], $classId);
        $this->applyExactFilter($lessonPlans, $availableColumns, ['section_id'], $sectionId);
        $this->applyExactFilter($lessonPlans, $availableColumns, ['date',], $targetDate);

        $lessonPlans = $lessonPlans
            ->orderByDesc($this->resolveSortColumn($availableColumns))
            ->orderByDesc('id')
            ->get();

        if ($lessonPlans->isEmpty()) {
            return $this->apiResponse(false, 'No lesson plans found for the selected date', [], 200);
        }

        return $this->apiResponse(true, 'Lesson plans fetched successfully', $lessonPlans);
    }

    private function applyExactFilter($query, array $availableColumns, array $candidates, mixed $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        foreach ($candidates as $column) {
            if (in_array($column, $availableColumns, true)) {
                $query->where($column, $value);
                return;
            }
        }
    }

    private function getLessonPlanColumns(array $availableColumns): array
    {
        $preferredColumns = [
            'id',
           'homework',
           'practice',
           'teaching',
           'date'
        ];

        $selectedColumns = array_values(array_intersect($preferredColumns, $availableColumns));

        return empty($selectedColumns) ? ['*'] : $selectedColumns;
    }

    private function resolveSortColumn(array $availableColumns): string
    {
        foreach (['date', 'plan_date', 'lesson_date', 'created_at'] as $column) {
            if (in_array($column, $availableColumns, true)) {
                return $column;
            }
        }

        return 'id';
    }
}
