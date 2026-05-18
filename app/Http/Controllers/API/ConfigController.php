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
                ],
                [
                    'key' => 'school_fees',
                    'enabled' => true,
                    'order' => 2,
                ],
                [
                    'key' => 'attendance',
                    'enabled' => false,
                    'order' => 3,
                ],
                [
                    'key' => 'exam_timetable',
                    'enabled' => true,
                    'order' => 4,
                ],
                [
                    'key' => 'report_card',
                    'enabled' => true,
                    'order' => 5,
                ],
                [
                    'key' => 'announcements',
                    'enabled' => true,
                    'order' => 6,
                ],
                [
                    'key' => 'school_events',
                    'enabled' => true,
                    'order' => 7,
                ],
                [
                    'key' => 'school_info',
                    'enabled' => false,
                    'order' => 8,
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
