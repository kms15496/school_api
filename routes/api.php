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
    });

    Route::group(['prefix' => 'exam', 'middleware' => ['check.request']], function () {
        Route::post('/', [\App\Http\Controllers\API\ExamController::class, 'getList']);
        Route::get('/{exam}', [\App\Http\Controllers\API\ExamController::class, 'getDetail']);  
    });
});
