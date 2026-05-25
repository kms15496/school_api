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
                    'key' => 'lesson_plan',
                    'enabled' => false,
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
                    'enabled' => false,
                    'order' => 3,
                    'image' => env('APP_URL') . '/app_icons/icon_attendance.png',
                ],
                [
                    'key' => 'exam_timetable',
                    'enabled' => false,
                    'order' => 4,
                    'image' => env('APP_URL') . '/app_icons/icon_exam.png',
                ],
                [
                    'key' => 'report_card',
                    'enabled' => false,
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
