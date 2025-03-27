<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Ferry;
use App\Models\Route;
use App\Models\ScheduleDate;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Display a listing of schedules.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Schedule::query();

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            // Default to active schedules
            $query->where('status', 'ACTIVE');
        }

        // Filter by departure_port and arrival_port if provided
        if ($request->has('departure_port') && $request->departure_port) {
            $query->whereHas('route', function ($q) use ($request) {
                $q->where('origin', $request->departure_port);
            });
        }

        if ($request->has('arrival_port') && $request->arrival_port) {
            $query->whereHas('route', function ($q) use ($request) {
                $q->where('destination', $request->arrival_port);
            });
        }

        // Filter by departure_date if provided
        if ($request->has('departure_date') && $request->departure_date) {
            $departureDate = Carbon::parse($request->departure_date);
            $dayOfWeek = $departureDate->dayOfWeek == 0 ? 7 : $departureDate->dayOfWeek;

            $query->where(function ($q) use ($dayOfWeek) {
                $q->whereRaw("FIND_IN_SET(?, days)", [$dayOfWeek]);
            });
        }

        // Include relationships
        $query->with(['route', 'ferry']);

        // Apply ordering
        $query->orderBy('departure_time');

        $schedules = $query->get();

        // Transform the data to ensure consistent types for client-side parsing
        $formattedSchedules = $schedules->map(function ($schedule) use ($request) {
            $scheduleData = $schedule->toArray();

            // Ensure all integer fields are actually integers, not null
            $scheduleData['id'] = (int)$scheduleData['id'];
            $scheduleData['route_id'] = (int)$scheduleData['route_id'];
            $scheduleData['ferry_id'] = (int)$scheduleData['ferry_id'];

            // Ensure ferry data has all required fields with proper types
            if (isset($scheduleData['ferry'])) {
                $scheduleData['ferry']['id'] = (int)$scheduleData['ferry']['id'];
                $scheduleData['ferry']['capacity_passenger'] = (int)($scheduleData['ferry']['capacity_passenger'] ?? 0);
                $scheduleData['ferry']['capacity_vehicle_motorcycle'] = (int)($scheduleData['ferry']['capacity_vehicle_motorcycle'] ?? 0);
                $scheduleData['ferry']['capacity_vehicle_car'] = (int)($scheduleData['ferry']['capacity_vehicle_car'] ?? 0);
                $scheduleData['ferry']['capacity_vehicle_bus'] = (int)($scheduleData['ferry']['capacity_vehicle_bus'] ?? 0);
                $scheduleData['ferry']['capacity_vehicle_truck'] = (int)($scheduleData['ferry']['capacity_vehicle_truck'] ?? 0);
            }

            // Ensure route data has proper types
            if (isset($scheduleData['route'])) {
                $scheduleData['route']['id'] = (int)$scheduleData['route']['id'];
                $scheduleData['route']['duration'] = (int)($scheduleData['route']['duration'] ?? 0);
                $scheduleData['route']['distance'] = (float)($scheduleData['route']['distance'] ?? 0);
                $scheduleData['route']['base_price'] = (float)($scheduleData['route']['base_price'] ?? 0);
                $scheduleData['route']['motorcycle_price'] = (float)($scheduleData['route']['motorcycle_price'] ?? 0);
                $scheduleData['route']['car_price'] = (float)($scheduleData['route']['car_price'] ?? 0);
                $scheduleData['route']['bus_price'] = (float)($scheduleData['route']['bus_price'] ?? 0);
                $scheduleData['route']['truck_price'] = (float)($scheduleData['route']['truck_price'] ?? 0);
            }

            // Add availability information if departure_date is provided
            if ($request->has('departure_date') && $request->departure_date) {
                $scheduleDate = ScheduleDate::where('schedule_id', $schedule->id)
                    ->where('date', $request->departure_date)
                    ->first();

                $remainingPassengerCapacity = $schedule->ferry ?
                    (int)$schedule->ferry->capacity_passenger : 0;

                if ($scheduleDate) {
                    if ($scheduleDate->status !== 'AVAILABLE') {
                        $scheduleData['is_available'] = false;
                        $scheduleData['unavailability_reason'] = 'Schedule is not available for this date';
                    } else {
                        $remainingPassengerCapacity -= (int)($scheduleDate->passenger_count ?? 0);
                        $scheduleData['is_available'] = true;
                        $scheduleData['remaining_passenger_capacity'] = $remainingPassengerCapacity;
                    }
                } else {
                    $scheduleData['is_available'] = true;
                    $scheduleData['remaining_passenger_capacity'] = $remainingPassengerCapacity;
                }
            }

            return $scheduleData;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'schedules' => $formattedSchedules
            ]
        ]);
    }

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
            if (
                $isAvailable &&
                $remainingPassengerCapacity >= $request->passenger_count &&
                (!$request->vehicle_count || $remainingVehicleCapacity >= $request->vehicle_count)
            ) {
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
     * @param Schedule $schedule
     * @return \Illuminate\View\View
     */
    public function show(Schedule $schedule, Request $request)
    {
        // Ensure relationships are properly loaded
        $schedule->load(['route', 'ferry']);

        // Filter by month and year if provided
        $query = ScheduleDate::where('schedule_id', $schedule->id);

        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }

        // Get paginated schedule dates
        $scheduleDates = $query->orderBy('date')->paginate(10);

        // Optionally get recent bookings if needed
        $bookings = []; // You can load bookings from your booking model if needed

        return view('admin.schedules.show', compact('schedule', 'scheduleDates', 'bookings'));
    }

    /**
     * Show the form for editing the specified schedule.
     *
     * @param Schedule $schedule
     * @return \Illuminate\View\View
     */
    public function edit(Schedule $schedule)
    {
        // Get all routes and ferries for dropdown options
        $routes = Route::where('status', 'ACTIVE')->get();
        $ferries = Ferry::where('status', 'ACTIVE')->get();

        return view('admin.schedules.edit', compact('schedule', 'routes', 'ferries'));
    }

    /**
     * Show the schedule date management page.
     *
     * @param Schedule $schedule
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function dates(Schedule $schedule, Request $request)
    {
        // Ensure relationships are properly loaded
        $schedule->load(['route', 'ferry']);

        // Filter by month, year, and status if provided
        $query = ScheduleDate::where('schedule_id', $schedule->id);

        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Get paginated schedule dates
        $scheduleDates = $query->orderBy('date')->paginate(10);

        return view('admin.schedules.dates', compact('schedule', 'scheduleDates'));
    }

    /**
     * Store a new schedule date.
     *
     * @param Schedule $schedule
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeDate(Schedule $schedule, Request $request)
    {
        $request->validate([
            'date_type' => 'required|in:single,range,days',
            'single_date' => 'required_if:date_type,single|date',
            'start_date' => 'required_if:date_type,range|date',
            'end_date' => 'required_if:date_type,range|date|after_or_equal:start_date',
            'days' => 'required_if:date_type,days|array',
            'days.*' => 'integer|between:0,7',
            'days_start_date' => 'required_if:date_type,days|date',
            'days_end_date' => 'required_if:date_type,days|date|after_or_equal:days_start_date',
            'status' => 'required|in:AVAILABLE,UNAVAILABLE',
        ]);

        try {
            // Handle different date types
            if ($request->date_type === 'single') {
                // Create single date
                ScheduleDate::updateOrCreate(
                    ['schedule_id' => $schedule->id, 'date' => $request->single_date],
                    ['status' => $request->status]
                );
            } elseif ($request->date_type === 'range') {
                // Create date range
                $startDate = Carbon::parse($request->start_date);
                $endDate = Carbon::parse($request->end_date);
                $currentDate = $startDate->copy();

                while ($currentDate->lte($endDate)) {
                    ScheduleDate::updateOrCreate(
                        ['schedule_id' => $schedule->id, 'date' => $currentDate->format('Y-m-d')],
                        ['status' => $request->status]
                    );
                    $currentDate->addDay();
                }
            } elseif ($request->date_type === 'days') {
                // Create specific days in date range
                $startDate = Carbon::parse($request->days_start_date);
                $endDate = Carbon::parse($request->days_end_date);
                $currentDate = $startDate->copy();

                $selectedDays = $request->days;

                while ($currentDate->lte($endDate)) {
                    $dayOfWeek = $currentDate->dayOfWeek;
                    // Convert Sunday from 0 to 7 to match your system
                    if ($dayOfWeek === 0) {
                        $dayOfWeek = 7;
                    }

                    if (in_array($dayOfWeek, $selectedDays)) {
                        ScheduleDate::updateOrCreate(
                            ['schedule_id' => $schedule->id, 'date' => $currentDate->format('Y-m-d')],
                            ['status' => $request->status]
                        );
                    }
                    $currentDate->addDay();
                }
            }

            return redirect()->route('admin.schedules.dates', $schedule)
                ->with('success', 'Tanggal jadwal berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan tanggal jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Update a schedule date.
     *
     * @param ScheduleDate $date
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateDate(ScheduleDate $date, Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'status' => 'required|in:AVAILABLE,UNAVAILABLE',
        ]);

        try {
            // Check if the date is being changed
            if ($date->date != $request->date) {
                // Make sure the new date doesn't already exist for this schedule
                $exists = ScheduleDate::where('schedule_id', $date->schedule_id)
                    ->where('date', $request->date)
                    ->where('id', '!=', $date->id)
                    ->exists();

                if ($exists) {
                    return back()->with('error', 'Tanggal tersebut sudah ada dalam jadwal.');
                }
            }

            $date->date = $request->date;
            $date->status = $request->status;
            $date->save();

            return redirect()->route('admin.schedules.dates', $date->schedule_id)
                ->with('success', 'Tanggal jadwal berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui tanggal jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Delete a schedule date.
     *
     * @param ScheduleDate $date
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteDate(ScheduleDate $date)
    {
        try {
            // Check if there are any bookings for this date
            // You would need to add this check based on your booking model structure

            $scheduleId = $date->schedule_id;
            $date->delete();

            return redirect()->route('admin.schedules.dates', $scheduleId)
                ->with('success', 'Tanggal jadwal berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus tanggal jadwal: ' . $e->getMessage());
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

    // Add this new method to handle API requests for a single schedule
    /**
     * Get a specific schedule (API method).
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSchedule($id)
    {
        try {
            $schedule = Schedule::with(['route', 'ferry'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'schedule' => $schedule
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
