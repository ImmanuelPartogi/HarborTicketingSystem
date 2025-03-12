<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ferry;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\ScheduleDate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @method \Illuminate\Routing\Controller middleware($middleware, array $options = [])
 */
class ScheduleController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display a listing of schedules.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $routeId = $request->query('route_id');
        $ferryId = $request->query('ferry_id');
        $status = $request->query('status');

        $query = Schedule::with(['route', 'ferry']);

        if ($routeId) {
            $query->where('route_id', $routeId);
        }

        if ($ferryId) {
            $query->where('ferry_id', $ferryId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->whereHas('route', function($q) use ($search) {
                $q->where('origin', 'like', "%{$search}%")
                  ->orWhere('destination', 'like', "%{$search}%");
            })->orWhereHas('ferry', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $schedules = $query->orderBy('route_id')->orderBy('departure_time')->paginate(15);

        $routes = Route::orderBy('origin')->orderBy('destination')->get();
        $ferries = Ferry::orderBy('name')->get();

        return view('admin.schedules.index', compact('schedules', 'routes', 'ferries', 'search', 'routeId', 'ferryId', 'status'));
    }

    /**
     * Show the form for creating a new schedule.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $routes = Route::where('status', 'ACTIVE')->orderBy('origin')->orderBy('destination')->get();
        $ferries = Ferry::where('status', 'ACTIVE')->orderBy('name')->get();

        return view('admin.schedules.create', compact('routes', 'ferries'));
    }

    /**
     * Store a newly created schedule.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'route_id' => 'required|exists:routes,id',
            'ferry_id' => 'required|exists:ferries,id',
            'departure_time' => 'required|date_format:H:i',
            'arrival_time' => 'required|date_format:H:i',
            'days' => 'required|array|min:1',
            'days.*' => 'required|in:1,2,3,4,5,6,7',
            'status' => 'required|in:ACTIVE,CANCELLED,DELAYED,FULL',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Format days as comma-separated string
            $days = implode(',', $request->days);

            Schedule::create([
                'route_id' => $request->route_id,
                'ferry_id' => $request->ferry_id,
                'departure_time' => $request->departure_time,
                'arrival_time' => $request->arrival_time,
                'days' => $days,
                'status' => $request->status,
            ]);

            return redirect()->route('admin.schedules.index')
                ->with('success', 'Schedule created successfully');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create schedule: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified schedule.
     *
     * @param Schedule $schedule
     * @return \Illuminate\View\View
     */
    public function show(Schedule $schedule)
    {
        $schedule->load(['route', 'ferry']);

        // Get upcoming schedule dates
        $today = Carbon::today();
        $upcomingDates = ScheduleDate::where('schedule_id', $schedule->id)
            ->where('date', '>=', $today)
            ->orderBy('date')
            ->paginate(10);

        return view('admin.schedules.show', compact('schedule', 'upcomingDates'));
    }

    /**
     * Show the form for editing the specified schedule.
     *
     * @param Schedule $schedule
     * @return \Illuminate\View\View
     */
    public function edit(Schedule $schedule)
    {
        $routes = Route::where('status', 'ACTIVE')->orderBy('origin')->orderBy('destination')->get();
        $ferries = Ferry::where('status', 'ACTIVE')->orderBy('name')->get();

        // Get days as array for checkbox selection
        $selectedDays = explode(',', $schedule->days);

        return view('admin.schedules.edit', compact('schedule', 'routes', 'ferries', 'selectedDays'));
    }

    /**
     * Update the specified schedule.
     *
     * @param Request $request
     * @param Schedule $schedule
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Schedule $schedule)
    {
        $validator = Validator::make($request->all(), [
            'route_id' => 'required|exists:routes,id',
            'ferry_id' => 'required|exists:ferries,id',
            'departure_time' => 'required|date_format:H:i',
            'arrival_time' => 'required|date_format:H:i',
            'days' => 'required|array|min:1',
            'days.*' => 'required|in:1,2,3,4,5,6,7',
            'status' => 'required|in:ACTIVE,CANCELLED,DELAYED,FULL',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Format days as comma-separated string
            $days = implode(',', $request->days);

            $schedule->update([
                'route_id' => $request->route_id,
                'ferry_id' => $request->ferry_id,
                'departure_time' => $request->departure_time,
                'arrival_time' => $request->arrival_time,
                'days' => $days,
                'status' => $request->status,
            ]);

            return redirect()->route('admin.schedules.index')
                ->with('success', 'Schedule updated successfully');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update schedule: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified schedule.
     *
     * @param Schedule $schedule
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Schedule $schedule)
    {
        try {
            // Check if schedule has any bookings
            if ($schedule->bookings()->count() > 0) {
                return back()->with('error', 'Cannot delete schedule with existing bookings');
            }

            $schedule->delete();

            return redirect()->route('admin.schedules.index')
                ->with('success', 'Schedule deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete schedule: ' . $e->getMessage());
        }
    }

    /**
     * Display schedule dates.
     *
     * @param Schedule $schedule
     * @return \Illuminate\View\View
     */
    public function dates(Schedule $schedule)
    {
        $schedule->load(['route', 'ferry']);

        // Get start date for calendar (current month)
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->addMonths(2)->endOfMonth();

        // Get all schedule dates within range
        $scheduleDates = ScheduleDate::where('schedule_id', $schedule->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy(function($item) {
                return $item->date->format('Y-m-d');
            });

        // Generate calendar data
        $calendar = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $month = $currentDate->format('F Y');
            $week = $currentDate->weekOfMonth;
            $day = $currentDate->format('j');
            $dayOfWeek = $currentDate->dayOfWeek;
            $fullDate = $currentDate->format('Y-m-d');

            // Check if schedule runs on this day of week
            $dayNumber = $dayOfWeek == 0 ? 7 : $dayOfWeek;
            $runsOnThisDay = in_array($dayNumber, explode(',', $schedule->days));

            // Get schedule date if exists
            $scheduleDate = $scheduleDates[$fullDate] ?? null;

            if (!isset($calendar[$month])) {
                $calendar[$month] = [];
            }

            if (!isset($calendar[$month][$week])) {
                $calendar[$month][$week] = [];
            }

            $calendar[$month][$week][] = [
                'day' => $day,
                'date' => $fullDate,
                'runs' => $runsOnThisDay,
                'status' => $scheduleDate ? $scheduleDate->status : null,
                'passengers' => $scheduleDate ? $scheduleDate->passenger_count : 0,
                'capacity' => $schedule->ferry->capacity_passenger,
                'schedule_date' => $scheduleDate,
            ];

            $currentDate->addDay();
        }

        return view('admin.schedules.dates', compact('schedule', 'calendar'));
    }

    /**
     * Store a specific schedule date.
     *
     * @param Request $request
     * @param Schedule $schedule
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeDates(Request $request, Schedule $schedule)
    {
        $validator = Validator::make($request->all(), [
            'dates' => 'required|array',
            'dates.*' => 'required|date_format:Y-m-d',
            'status' => 'required|in:AVAILABLE,FULL,CANCELLED,DEPARTED,WEATHER_ISSUE',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            foreach ($request->dates as $date) {
                ScheduleDate::updateOrCreate(
                    ['schedule_id' => $schedule->id, 'date' => $date],
                    ['status' => $request->status]
                );
            }

            return back()->with('success', 'Schedule dates updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update schedule dates: ' . $e->getMessage());
        }
    }

    /**
     * Update a specific schedule date.
     *
     * @param Request $request
     * @param Schedule $schedule
     * @param string $date
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateDate(Request $request, Schedule $schedule, $date)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:AVAILABLE,FULL,CANCELLED,DEPARTED,WEATHER_ISSUE',
            'passenger_count' => 'nullable|integer|min:0',
            'motorcycle_count' => 'nullable|integer|min:0',
            'car_count' => 'nullable|integer|min:0',
            'bus_count' => 'nullable|integer|min:0',
            'truck_count' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $scheduleDate = ScheduleDate::updateOrCreate(
                ['schedule_id' => $schedule->id, 'date' => $date],
                [
                    'status' => $request->status,
                    'passenger_count' => $request->passenger_count ?? 0,
                    'motorcycle_count' => $request->motorcycle_count ?? 0,
                    'car_count' => $request->car_count ?? 0,
                    'bus_count' => $request->bus_count ?? 0,
                    'truck_count' => $request->truck_count ?? 0,
                ]
            );

            return back()->with('success', 'Schedule date updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update schedule date: ' . $e->getMessage());
        }
    }
}
