<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {

    $appId = 'E09POT2WLZ';
    $secret = 'X31ewI157jskV2O6ChnAEWrsqIlyE2hbFadrjYvU';
    $baseUrl = 'https://cloud.scorm.com/api/v2';

    $allCourses = [];
    $more = null;

    do {

        $response = Http::withBasicAuth($appId, $secret)

            ->get($baseUrl . '/courses', array_filter([

                'more' => $more,

            ]));

        if (!$response->successful()) {

            return response()->json([

                'status' => false,

                'message' => 'Failed to fetch SCORM Cloud courses',

                'error' => $response->json(),

            ], $response->status());

        }

        $data = $response->json();

        foreach ($data['courses'] ?? [] as $course) {

            $allCourses[] = [

                'id' => trim((string) ($course['id'] ?? '')),

                'title' => $course['title'] ?? null,

            ];

        }

        $more = $data['more'] ?? null;

    } while ($more);

    $scormCourses = collect($allCourses);

    $localPackages = collect(DB::select("

        SELECT id, course_id

        FROM xapi_packages

    "));

    $localCourseIds = $localPackages

        ->pluck('course_id')

        ->filter()

        ->map(fn($id) => trim((string) $id))

        ->unique()

        ->values();

    $scormCourseIds = $scormCourses

        ->pluck('id')

        ->filter()

        ->map(fn($id) => trim((string) $id))

        ->unique()

        ->values();

    $missingCourses = $scormCourses

        ->filter(function ($course) use ($localCourseIds) {

            return !$localCourseIds->contains(trim((string) $course['id']));

        })

        ->values();

    return response()->json([

        'status' => true,

        'scorm_cloud_total' => $scormCourses->count(),

        'scorm_cloud_unique_total' => $scormCourseIds->count(),

        'local_db_total' => $localPackages->count(),

        'local_unique_course_id_total' => $localCourseIds->count(),

        'missing_count' => $missingCourses->count(),

        'missing_courses' => $missingCourses,

    ]);
});
