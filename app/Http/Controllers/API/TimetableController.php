<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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

        $dayValues = $this->normalizeDayFilter($day);
        if (empty($dayValues)) {
            return $this->apiResponse(false, 'Invalid day', [], 200);
        }

        if (!Schema::hasTable('timetables')) {
            return $this->apiResponse(false, 'Timetable data source not found', [], 500);
        }

        $availableColumns = Schema::getColumnListing('timetables');
        $dayColumn = $this->resolveColumn($availableColumns, ['day', 'weekday', 'day_of_week']);

        if (!$dayColumn) {
            return $this->apiResponse(false, 'Timetable table is misconfigured', [], 500);
        }

        $query = Timetable::query()
            ->select($this->getTimetableColumns($availableColumns));

        $this->applyExactFilter($query, $availableColumns, ['school_id'], $schoolId);
        $this->applyExactFilter($query, $availableColumns, ['academic_year_id', 'aay'], $academicYearId);
        $this->applyExactFilter($query, $availableColumns, ['school_class_id', 'class_id', 'grade_id', 'grade'], $classId);
        $this->applyExactFilter($query, $availableColumns, ['section_id'], $sectionId);

        $query->whereIn($dayColumn, $dayValues);

        if (in_array('subject_id', $availableColumns, true)) {
            $query->with([
                'subject' => function ($subjectQuery) {
                    $subjectQuery->select(['id', 'name', 'code', 'optional']);
                },
            ]);
        }

        foreach ($this->getSortColumns($availableColumns) as $sortColumn) {
            $query->orderBy($sortColumn);
        }

        $timetables = $query->get();

        if ($timetables->isEmpty()) {
            return $this->apiResponse(false, 'Timetable not found for the selected day', [], 200);
        }

        return $this->apiResponse(true, 'Timetable fetched successfully', $timetables);
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

    private function getTimetableColumns(array $availableColumns): array
    {
        $preferredColumns = [
            'id',
            'school_id',
            'academic_year_id',
            'school_class_id',
            'class_id',
            'section_id',
            'subject_id',
            'day',
            'weekday',
            'day_of_week',
            'period',
            'period_no',
            'start_time',
            'end_time',
            'order',
            'created_at',
            'updated_at',
        ];

        $selectedColumns = array_values(array_intersect($preferredColumns, $availableColumns));

        return empty($selectedColumns) ? ['*'] : $selectedColumns;
    }

    private function getSortColumns(array $availableColumns): array
    {
        $preferredColumns = ['order', 'period', 'period_no', 'start_time', 'id'];

        return array_values(array_intersect($preferredColumns, $availableColumns));
    }

    private function normalizeDayFilter(string $day): array
    {
        $days = [
            'monday' => ['monday', 'mon', '1'],
            'tuesday' => ['tuesday', 'tue', 'tues', '2'],
            'wednesday' => ['wednesday', 'wed', '3'],
            'thursday' => ['thursday', 'thu', 'thur', 'thurs', '4'],
            'friday' => ['friday', 'fri', '5'],
            'saturday' => ['saturday', 'sat', '6'],
            'sunday' => ['sunday', 'sun', '7', '0'],
        ];

        $normalized = strtolower(trim($day));
        foreach ($days as $fullDay => $aliases) {
            if (in_array($normalized, $aliases, true)) {
                $shortDay = ucfirst(substr($fullDay, 0, 3));
                $fullDay = ucfirst($fullDay);

                return array_values(array_unique([
                    $fullDay,
                    strtolower($fullDay),
                    strtoupper($fullDay),
                    $shortDay,
                    strtolower($shortDay),
                    strtoupper($shortDay),
                    ...array_filter($aliases, fn ($alias) => is_numeric($alias)),
                ]));
            }
        }

        return [];
    }

    private function resolveColumn(array $availableColumns, array $candidates): ?string
    {
        foreach ($candidates as $column) {
            if (in_array($column, $availableColumns, true)) {
                return $column;
            }
        }

        return null;
    }
}
