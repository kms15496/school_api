<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class ConfigController extends Controller
{
    public function getConfig()
    {
        $config = [
            'screen' => [
                 [
                    'key' => 'timetable',
                    'enabled' => true,
                    'order' => 1,
                    'image' => env('APP_URL') . '/app_icons/icon_lesson.png',
                ],
                [
                    'key' => 'lesson_plan',
                    'enabled' => true,
                    'order' => 1,
                    'image' => env('APP_URL') . '/app_icons/icon_lesson.png',
                ],
                [
                    'key' => 'school_fees',
                    'enabled' => true,
                    'order' => 2,
                    'image' => env('APP_URL') . '/app_icons/icon_fees.png',
                ],
                [
                    'key' => 'attendance',
                    'enabled' => true,
                    'order' => 3,
                    'image' => env('APP_URL') . '/app_icons/icon_attendance.png',
                ],
                [
                    'key' => 'exam_timetable',
                    'enabled' => true,
                    'order' => 4,
                    'image' => env('APP_URL') . '/app_icons/icon_exam.png',
                ],
                [
                    'key' => 'report_card',
                    'enabled' => true,
                    'order' => 5,
                    'image' => env('APP_URL') . '/app_icons/icon_result.png',
                ],
                [
                    'key' => 'announcements',
                    'enabled' => true,
                    'order' => 6,
                    'image' => env('APP_URL') . '/app_icons/icon_information.png',
                ],
                [
                    'key' => 'school_events',
                    'enabled' => true,
                    'order' => 7,
                    'image' => env('APP_URL') . '/app_icons/icon_events.png',
                ],
                [
                    'key' => 'school_info',
                    'enabled' => false,
                    'order' => 8,
                    'image' => env('APP_URL') . '/app_icons/icon_school.png',
                ],
            ],
        ];

        return response()->json([
            'status' => true,
            'message' => 'Config retrieved successfully',
            'data' => $config,
        ]);
    }
}
