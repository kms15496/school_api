<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function getConfig()
    {
        $config = [
            'screen' => [
                [
                    'key' => 'lesson_plan',
                    'enabled' => true,
                    'order' => 1,
                    'image' => asset('app_icons/icon_lesson.png'),
                ],
                [
                    'key' => 'school_fees',
                    'enabled' => true,
                    'order' => 2,
                    'image' => asset('app_icons/icon_fees.png'),
                ],
                [
                    'key' => 'attendance',
                    'enabled' => true,
                    'order' => 3,
                    'image' => asset('app_icons/icon_attendance.png'),
                ],
                [
                    'key' => 'exam_timetable',
                    'enabled' => true,
                    'order' => 4,
                    'image' => asset('app_icons/icon_exam.png'),
                ],
                [
                    'key' => 'report_card',
                    'enabled' => true,
                    'order' => 5,
                    'image' => asset('app_icons/icon_result.png'),
                ],
                [
                    'key' => 'announcements',
                    'enabled' => true,
                    'order' => 6,
                    'image' => asset('app_icons/icon_information.png'),
                ],
                [
                    'key' => 'school_events',
                    'enabled' => true,
                    'order' => 7,
                    'image' => asset('app_icons/icon_events.png'),
                ],
                [
                    'key' => 'school_info',
                    'enabled' => false,
                    'order' => 8,
                    'image' => asset('app_icons/icon_school.png'),
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
