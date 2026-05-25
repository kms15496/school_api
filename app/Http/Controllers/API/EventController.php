<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class EventController extends Controller
{
    public function getList(Request $request)
    {
        $schoolId = $request->input('school_id');
        $academicYearId = $request->input('aay');
        $type = $request->input('type');

        if (!$schoolId || !$academicYearId) {
            return $this->apiResponse(false, 'Invalid request context', [], 200);
        }

        if (!Schema::hasTable('events')) {
            return $this->apiResponse(false, 'Event data source not found', null, 500);
        }



        $query = Event::query()
            ->select(['id', 'school_id', 'academic_year_id', 'date', 'title', 'body', 'created_at', 'updated_at'])
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $academicYearId)
            ->orderByDesc('date')
            ->orderByDesc('id');

        if ($type === 'home') {
            $query->limit(1);
        }

        $events = $query->get();

        $events->makeHidden(['media']);

        if ($events->isEmpty()) {
            return $this->apiResponse(false, 'No events found for the school and academic year', [], 200);
        }

        return $this->apiResponse(true, 'Events fetched successfully', $events);
    }

    public function getDetail(Request $request, Event $event)
    {
        $schoolId = $request->input('school_id');
        $academicYearId = $request->input('aay');

        if (!$schoolId || !$academicYearId) {
            return $this->apiResponse(false, 'Invalid request context', [], 200);
        }

        if ($event->school_id != $schoolId || $event->academic_year_id != $academicYearId) {
            return $this->apiResponse(false, 'Event not found for the school and academic year', [], 200);
        }

        $event->makeHidden(['media']);


        return $this->apiResponse(true, 'Event details fetched successfully', $event);
    }
}
