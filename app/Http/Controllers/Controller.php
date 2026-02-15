<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function apiResponse(
        bool $status,
        string $message,
        mixed $data = null,
        int $code = 200
    ) {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}
