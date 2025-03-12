<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Route;
use App\Models\ScheduleDate;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Search for available schedules.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $request->validate([
            'origin' => 'required|string',
            'destination' => 'required|string',
            'date' => 'required|date_format:Y-m-d',
            'passenger_count' => 'required|integer|min:1',
            'vehicle_type' => 'nullable|string|in:MOTORCYCLE,CAR,BUS,TRUCK',
            'vehicle_count' => 'nullable|integer|min:0',
        ]);

        // Find routes matching origin and destination
        $routes = Route::where('origin', $request->origin)
            ->where('destination', $request->destination)
            ->where('status', 'ACTIVE')
            ->get();

        if ($routes->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'schedules' => []
                ],
                'message' => 'No routes found for the specified origin and destination'
            ]);
        }

        $routeIds = $routes->pluck('id');
        $searchDate = Carbon::parse($request->date);
        $dayOfWeek = $searchDate->dayOfWeek == 0 ? 7 : $searchDate->dayOfWeek;

        // Find schedules for these routes running on the specified day
        $schedules = Schedule::whereIn('route_id', $routeIds)
            ->where('status', 'ACTIVE')
            ->where(function ($query) use ($dayOfWeek) {
                $query->whereRaw("FIND_IN_SET(?, days)", [$dayOfWeek]);
            })
            ->with(['route', 'ferry'])
            ->get();

        // Filter schedules based on availability for the specific date
        $availableSchedules = [];
        foreach ($schedules as $schedule) {
            // Check if schedule date exists and is available
            $scheduleDate = ScheduleDate::where('schedule_id', $schedule->id)
                ->where('date', $searchDate->format('Y-m-d'))
                ->first();

            // If schedule date doesn't exist, it means it's available by default
            $isAvailable = true;
            $remainingPassengerCapacity = $schedule->ferry->capacity_passenger;
            $remainingVehicleCapacity = 0;

            if ($request->vehicle_type) {
                switch ($request->vehicle_type) {
                    case 'MOTORCYCLE':
                        $remainingVehicleCapacity = $schedule->ferry->capacity_vehicle_motorcycle;
                        break;
                    case 'CAR':
                        $remainingVehicleCapacity = $schedule->ferry->capacity_vehicle_car;
                        break;
                    case 'BUS':
                        $remainingVehicleCapacity = $schedule->ferry->capacity_vehicle_bus;
                        break;
                    case 'TRUCK':
                        $remainingVehicleCapacity = $schedule->ferry->capacity_vehicle_truck;
                        break;
                }
            }

            if ($scheduleDate) {
                if ($scheduleDate->status !== 'AVAILABLE') {
                    $isAvailable = false;
                } else {
                    $remainingPassengerCapacity -= $scheduleDate->passenger_count;

                    if ($request->vehicle_type) {
                        switch ($request->vehicle_type) {
                            case 'MOTORCYCLE':
                                $remainingVehicleCapacity -= $scheduleDate->motorcycle_count;
                                break;
                            case 'CAR':
                                $remainingVehicleCapacity -= $scheduleDate->car_count;
                                break;
                            case 'BUS':
                                $remainingVehicleCapacity -= $scheduleDate->bus_count;
                                break;
                            case 'TRUCK':
                                $remainingVehicleCapacity -= $scheduleDate->truck_count;
                                break;
                        }
                    }
                }
            }

            // Check if enough capacity for passengers and vehicles
            if ($isAvailable &&
                $remainingPassengerCapacity >= $request->passenger_count &&
                (!$request->vehicle_count || $remainingVehicleCapacity >= $request->vehicle_count)) {
                $schedule->remaining_passenger_capacity = $remainingPassengerCapacity;
                $schedule->remaining_vehicle_capacity = $remainingVehicleCapacity;
                $availableSchedules[] = $schedule;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'schedules' => $availableSchedules
            ]
        ]);
    }

    /**
     * Display the specified schedule.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $schedule = Schedule::with(['route', 'ferry'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'schedule' => $schedule
            ]
        ]);
    }

    /**
     * Check availability for a specific schedule and date.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'date' => 'required|date_format:Y-m-d',
            'passenger_count' => 'required|integer|min:1',
            'vehicle_type' => 'nullable|string|in:MOTORCYCLE,CAR,BUS,TRUCK',
            'vehicle_count' => 'nullable|integer|min:0',
        ]);

        $schedule = Schedule::with(['ferry'])->findOrFail($request->schedule_id);
        $searchDate = Carbon::parse($request->date);
        $dayOfWeek = $searchDate->dayOfWeek == 0 ? 7 : $searchDate->dayOfWeek;

        // Check if schedule runs on this day
        if (!in_array($dayOfWeek, explode(',', $schedule->days))) {
            return response()->json([
                'success' => true,
                'data' => [
                    'available' => false,
                    'reason' => 'Schedule does not run on this day'
                ]
            ]);
        }

        // Check schedule date availability
        $scheduleDate = ScheduleDate::where('schedule_id', $schedule->id)
            ->where('date', $searchDate->format('Y-m-d'))
            ->first();

        $remainingPassengerCapacity = $schedule->ferry->capacity_passenger;
        $remainingVehicleCapacity = 0;

        if ($request->vehicle_type) {
            switch ($request->vehicle_type) {
                case 'MOTORCYCLE':
                    $remainingVehicleCapacity = $schedule->ferry->capacity_vehicle_motorcycle;
                    break;
                case 'CAR':
                    $remainingVehicleCapacity = $schedule->ferry->capacity_vehicle_car;
                    break;
                case 'BUS':
                    $remainingVehicleCapacity = $schedule->ferry->capacity_vehicle_bus;
                    break;
                case 'TRUCK':
                    $remainingVehicleCapacity = $schedule->ferry->capacity_vehicle_truck;
                    break;
            }
        }

        if ($scheduleDate) {
            if ($scheduleDate->status !== 'AVAILABLE') {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'available' => false,
                        'reason' => 'Schedule is not available for this date'
                    ]
                ]);
            }

            $remainingPassengerCapacity -= $scheduleDate->passenger_count;

            if ($request->vehicle_type) {
                switch ($request->vehicle_type) {
                    case 'MOTORCYCLE':
                        $remainingVehicleCapacity -= $scheduleDate->motorcycle_count;
                        break;
                    case 'CAR':
                        $remainingVehicleCapacity -= $scheduleDate->car_count;
                        break;
                    case 'BUS':
                        $remainingVehicleCapacity -= $scheduleDate->bus_count;
                        break;
                    case 'TRUCK':
                        $remainingVehicleCapacity -= $scheduleDate->truck_count;
                        break;
                }
            }
        }

        // Check if enough capacity for passengers and vehicles
        $isAvailable = $remainingPassengerCapacity >= $request->passenger_count &&
            (!$request->vehicle_count || $remainingVehicleCapacity >= $request->vehicle_count);

        return response()->json([
            'success' => true,
            'data' => [
                'available' => $isAvailable,
                'remaining_passenger_capacity' => $remainingPassengerCapacity,
                'remaining_vehicle_capacity' => $remainingVehicleCapacity,
                'reason' => !$isAvailable ? 'Not enough capacity' : null
            ]
        ]);
    }
}
