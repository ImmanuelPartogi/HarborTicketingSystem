<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\ScheduleDate;
use App\Services\ScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    protected $scheduleService;

    /**
     * Create a new controller instance.
     *
     * @param ScheduleService $scheduleService
     */
    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Search for available schedules.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin' => 'required|string|max:100',
            'destination' => 'required|string|max:100',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'passenger_count' => 'required|integer|min:1|max:500',
            'vehicle_type' => 'nullable|string|in:MOTORCYCLE,CAR,BUS,TRUCK',
            'vehicle_count' => 'nullable|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $schedules = $this->scheduleService->searchAvailableSchedules(
                $request->origin,
                $request->destination,
                $request->date,
                $request->passenger_count,
                $request->vehicle_type,
                $request->vehicle_count
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'schedules' => $schedules,
                    'search_params' => [
                        'origin' => $request->origin,
                        'destination' => $request->destination,
                        'date' => $request->date,
                        'passenger_count' => $request->passenger_count,
                        'vehicle_type' => $request->vehicle_type,
                        'vehicle_count' => $request->vehicle_count
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Schedule search error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to search schedules',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Display the specified schedule.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $schedule = Schedule::with(['route', 'ferry'])->findOrFail($id);

            // Get availability for next 30 days
            $today = Carbon::today();
            $dateRange = [];
            for ($i = 0; $i < 30; $i++) {
                $date = $today->copy()->addDays($i);
                $dateRange[] = $date->format('Y-m-d');
            }

            $availabilityData = $this->scheduleService->getScheduleAvailabilityForDates($schedule, $dateRange);

            return response()->json([
                'success' => true,
                'data' => [
                    'schedule' => $schedule,
                    'availability' => $availabilityData
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule not found',
                'error' => 'The requested schedule could not be found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving schedule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve schedule',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Check availability for a specific schedule and date.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schedule_id' => 'required|exists:schedules,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'passenger_count' => 'required|integer|min:1|max:500',
            'vehicle_type' => 'nullable|string|in:MOTORCYCLE,CAR,BUS,TRUCK',
            'vehicle_count' => 'nullable|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $availabilityResult = $this->scheduleService->checkScheduleAvailability(
                $request->schedule_id,
                $request->date,
                $request->passenger_count,
                $request->vehicle_type,
                $request->vehicle_count
            );

            return response()->json([
                'success' => true,
                'data' => $availabilityResult
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking availability: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check availability',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Get schedule availability for a date range.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailabilityForDateRange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schedule_id' => 'required|exists:schedules,id',
            'start_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'passenger_count' => 'required|integer|min:1',
            'vehicle_type' => 'nullable|string|in:MOTORCYCLE,CAR,BUS,TRUCK',
            'vehicle_count' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Limit range to maximum 90 days
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            if ($startDate->diffInDays($endDate) > 90) {
                $endDate = $startDate->copy()->addDays(90);
            }

            $schedule = Schedule::findOrFail($request->schedule_id);

            $dateRange = [];
            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                $dateRange[] = $currentDate->format('Y-m-d');
                $currentDate->addDay();
            }

            $availabilityData = $this->scheduleService->getScheduleAvailabilityForDates(
                $schedule,
                $dateRange,
                $request->passenger_count,
                $request->vehicle_type,
                $request->vehicle_count
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'schedule_id' => $schedule->id,
                    'availability' => $availabilityData,
                    'date_range' => [
                        'start' => $startDate->format('Y-m-d'),
                        'end' => $endDate->format('Y-m-d')
                    ]
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule not found',
                'error' => 'The requested schedule could not be found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving availability for date range: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve availability',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Get popular routes and their schedules.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPopularRoutes()
    {
        try {
            $popularRoutes = $this->scheduleService->getPopularRoutes();

            return response()->json([
                'success' => true,
                'data' => [
                    'popular_routes' => $popularRoutes
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving popular routes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve popular routes',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Get schedules for a specific route.
     *
     * @param  int  $routeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSchedulesForRoute($routeId)
    {
        try {
            $schedules = $this->scheduleService->getSchedulesForRoute($routeId);

            return response()->json([
                'success' => true,
                'data' => [
                    'schedules' => $schedules
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving schedules for route: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve schedules',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }
}
