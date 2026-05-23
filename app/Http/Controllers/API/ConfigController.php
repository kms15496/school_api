<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

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
                    'image' => url('/api/app-icons/icon_lesson.png'),
                ],
                [
                    'key' => 'school_fees',
                    'enabled' => true,
                    'order' => 2,
                    'image' => url('/api/app-icons/icon_fees.png'),
                ],
                [
                    'key' => 'attendance',
                    'enabled' => true,
                    'order' => 3,
                    'image' => url('/api/app-icons/icon_attendance.png'),
                ],
                [
                    'key' => 'exam_timetable',
                    'enabled' => true,
                    'order' => 4,
                    'image' => url('/api/app-icons/icon_exam.png'),
                ],
                [
                    'key' => 'report_card',
                    'enabled' => true,
                    'order' => 5,
                    'image' => url('/api/app-icons/icon_result.png'),
                ],
                [
                    'key' => 'announcements',
                    'enabled' => true,
                    'order' => 6,
                    'image' => url('/api/app-icons/icon_information.png'),
                ],
                [
                    'key' => 'school_events',
                    'enabled' => true,
                    'order' => 7,
                    'image' => url('/api/app-icons/icon_events.png'),
                ],
                [
                    'key' => 'school_info',
                    'enabled' => false,
                    'order' => 8,
                    'image' => url('/api/app-icons/icon_school.png'),
                ],
            ],
        ];

        return response()->json([
            'status' => true,
            'message' => 'Config retrieved successfully',
            'data' => $config,
        ]);
    }

    public function getAppIcon(string $filename)
    {
        abort_unless(preg_match('/\Aicon_[a-z_]+\.png\z/', $filename), 404);

        $path = public_path('app_icons/' . $filename);

        abort_unless(File::isFile($path), 404);

        return response()->file($path, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
