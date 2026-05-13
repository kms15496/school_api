<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return response()->json([
        'status' => true,
        'message' => 'User success',
        'data' => $request->user(),
    ]);
})->middleware('auth:sanctum');

// Route::post('/lotgin', [\App\Http\Controllers\API\LoginController::class, 'login']);
Route::post('/login', [\App\Http\Controllers\API\LoginController::class, 'parentLogin']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/get-info', [\App\Http\Controllers\API\InfoController::class, 'getInfo']);

    Route::group(['prefix' => 'student', 'middleware' => ['check.student.parent', 'check.request']], function () {

        Route::get('/{student}', [\App\Http\Controllers\API\StudentController::class, 'show']);
        Route::post('/attendance', [\App\Http\Controllers\API\StudentController::class, 'getAttendance']);
        Route::post('/exam-timetable', [\App\Http\Controllers\API\ExamController::class, 'getExamTimetable']);
        Route::post('/report-card', [\App\Http\Controllers\API\ExamController::class, 'getReportCard']);
    });

    Route::group(['prefix' => 'exam', 'middleware' => ['check.request']], function () {
        Route::post('/', [\App\Http\Controllers\API\ExamController::class, 'getList']);
        Route::get('/{exam}', [\App\Http\Controllers\API\ExamController::class, 'getDetail']);

        Route::get('/{exam}/sessions', [\App\Http\Controllers\API\ExamController::class, 'getSessions']);
    });

    Route::group(['prefix' => 'event', 'middleware' => ['check.request']], function () {
        Route::get('/', [\App\Http\Controllers\API\EventController::class, 'getList']);
        Route::get('/{event}', [\App\Http\Controllers\API\EventController::class, 'getDetail']);
    });

    Route::group(['prefix' => 'announcement', 'middleware' => ['check.request']], function () {
        Route::get('/', [\App\Http\Controllers\API\AnnouncementController::class, 'getList']);
        Route::get('/{announcement}', [\App\Http\Controllers\API\AnnouncementController::class, 'getDetail']);
    });

    Route::group(['prefix' => 'lesson-plan', 'middleware' => ['check.request']], function () {
        Route::get('/', [\App\Http\Controllers\API\LessonPlanController::class, 'getList']);
    });

    Route::post('/set-fcm-token', [\App\Http\Controllers\API\DeviceController::class, 'setFcmToken']);

    Route::get('/fees', [\App\Http\Controllers\API\FeeController::class, 'getFees'])->middleware('check.request');

    Route::post('/invoice/{id}/make-payment', [\App\Http\Controllers\API\InvoiceController::class, 'makePayment'])->middleware('check.request');
    Route::post('/invoice/{id}/check-status', [\App\Http\Controllers\API\InvoiceController::class, 'checkInvoiceStatus'])->middleware('check.request');


    Route::get('/fee/{fee}/make-payment', [\App\Http\Controllers\API\FeeController::class, 'makePayment'])->middleware('check.request');

    Route::get('/subjects', [\App\Http\Controllers\API\StudentController::class, 'getSubjects'])->middleware('check.request');
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
