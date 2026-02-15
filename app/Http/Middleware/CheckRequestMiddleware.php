<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Models\StudentAcademicYear;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->filled(['school_id', 'student_id'])) {
            return response()->json([
                'status' => false,
                'message' => 'Missing required parameters: school_id and student_id',
                'data' => null,
            ], 400);
        }

        $schoolId = $request->input('school_id');
        $studentId = $request->input('student_id');

        $cacheTtlSeconds = 300;
        $ctx = Cache::remember(
            "request_ctx:school:{$schoolId}:student:{$studentId}",
            $cacheTtlSeconds,
            function () use ($schoolId, $studentId) {
                $allowAcademicYear = Setting::where('school_id', $schoolId)
                    ->value('allow_academic_year');

                if (!$allowAcademicYear) {
                    return [
                        'aay' => null,
                        'class_id' => null,
                        'section_id' => null,
                    ];
                }

                $studentRow = StudentAcademicYear::where('student_id', $studentId)
                    ->where('academic_year_id', $allowAcademicYear)
                    ->first(['school_class_id', 'section_id']);

                return [
                    'aay' => $allowAcademicYear,
                    'class_id' => $studentRow?->school_class_id,
                    'section_id' => $studentRow?->section_id,
                ];
            }
        );

        if ($ctx['aay'] === null) {
            return response()->json([
                'status' => false,
                'message' => 'Academic year not configured for school',
                'data' => null,
            ], 404);
        }

        if ($ctx['class_id'] === null || $ctx['section_id'] === null) {
            return response()->json([
                'status' => false,
                'message' => 'Student academic year data not found',
                'data' => null,
            ], 404);
        }

        $request->merge($ctx);



        return $next($request);
    }
}
