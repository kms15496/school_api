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

