<?php

namespace App\Http\Middleware;

use App\Models\Student;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckStudentParentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $parent = $request->user();
        if (!$parent) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'data' => null,
            ], 401);
        }

        $studentId = $request->input('student_id') ?? $request->route('student');
        if (!$studentId) {
            return response()->json([
                'status' => false,
                'message' => 'Missing required parameter: student_id',
                'data' => null,
            ], 400);
        }

        $parentId = $parent->getKey();
        $cacheTtlSeconds = 300;
        $isAllowed = Cache::remember(
            "parent:{$parentId}:student:{$studentId}:allowed",
            $cacheTtlSeconds,
            fn () => Student::where('id', $studentId)
                ->where('parent_id', $parentId)
                ->exists()
        );

        if (!$isAllowed) {
            return response()->json([
                'status' => false,
                'message' => 'Student does not belong to parent',
                'data' => null,
            ], 403);
        }

        return $next($request);
    }
}
