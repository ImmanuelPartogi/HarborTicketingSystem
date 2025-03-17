<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\ScheduleDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @method \Illuminate\Routing\Controller middleware($middleware, array $options = [])
 */
class RouteController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display a listing of routes.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $query = Route::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('origin', 'like', "%{$search}%")
                    ->orWhere('destination', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $routes = $query->orderBy('origin')->orderBy('destination')->paginate(15);

        return view('admin.routes.index', compact('routes', 'search', 'status'));
    }

    /**
     * Show the form for creating a new route.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.routes.create');
    }

    /**
     * Store a newly created route.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255|different:origin',
            'distance' => 'nullable|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'base_price' => 'required|numeric|min:0',
            'motorcycle_price' => 'required|numeric|min:0',
            'car_price' => 'required|numeric|min:0',
            'bus_price' => 'required|numeric|min:0',
            'truck_price' => 'required|numeric|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE,WEATHER_ISSUE',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if route already exists
        $existingRoute = Route::where('origin', $request->origin)
            ->where('destination', $request->destination)
            ->first();

        if ($existingRoute) {
            return back()->withInput()
                ->with('error', 'Route already exists with the same origin and destination');
        }

        try {
            Route::create($request->all());

            return redirect()->route('admin.routes.index')
                ->with('success', 'Route created successfully');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create route: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified route.
     *
     * @param Route $route
     * @return \Illuminate\View\View
     */
    public function show(Route $route)
    {
        // Get schedules for this route
        $schedules = Schedule::with('ferry')
            ->where('route_id', $route->id)
            ->orderBy('departure_time')
            ->get();

        return view('admin.routes.show', compact('route', 'schedules'));
    }

    /**
     * Show the form for editing the specified route.
     *
     * @param Route $route
     * @return \Illuminate\View\View
     */
    public function edit(Route $route)
    {
        return view('admin.routes.edit', compact('route'));
    }

    /**
     * Update the specified route.
     *
     * @param Request $request
     * @param Route $route
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Route $route)
    {
        $validator = Validator::make($request->all(), [
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255|different:origin',
            'distance' => 'nullable|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'base_price' => 'required|numeric|min:0',
            'motorcycle_price' => 'required|numeric|min:0',
            'car_price' => 'required|numeric|min:0',
            'bus_price' => 'required|numeric|min:0',
            'truck_price' => 'required|numeric|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE,WEATHER_ISSUE',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if route already exists, excluding current route
        $existingRoute = Route::where('origin', $request->origin)
            ->where('destination', $request->destination)
            ->where('id', '!=', $route->id)
            ->first();

        if ($existingRoute) {
            return back()->withInput()
                ->with('error', 'Route already exists with the same origin and destination');
        }

        try {
            $route->update($request->all());

            return redirect()->route('admin.routes.index')
                ->with('success', 'Route updated successfully');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update route: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified route.
     *
     * @param Route $route
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Route $route)
    {
        try {
            // Check if route has any schedules
            if (Schedule::where('route_id', $route->id)->exists()) {
                return back()->with('error', 'Cannot delete route with existing schedules');
            }

            $route->delete();

            return redirect()->route('admin.routes.index')
                ->with('success', 'Route deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete route: ' . $e->getMessage());
        }
    }

    /**
     * Update the status of a route with a more flexible approach.
     *
     * @param Request $request
     * @param Route $route
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, Route $route)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:ACTIVE,INACTIVE,WEATHER_ISSUE',
            'reason' => 'nullable|string|max:255',
            'apply_to_schedules' => 'boolean',
            'start_time' => 'nullable|required_if:apply_to_schedules,1|date_format:H:i',
            'end_time' => 'nullable|required_if:apply_to_schedules,1|date_format:H:i',
            'affect_days' => 'nullable|integer|min:0|max:30'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            // Update route status
            $oldStatus = $route->status;
            $newStatus = $request->status;

            $route->status = $newStatus;
            $route->status_reason = $request->reason ?? null;
            $route->save();

            $affectedSchedulesCount = 0;
            $affectedDatesCount = 0;

            // If checkbox apply_to_schedules is checked or route changes to INACTIVE/WEATHER_ISSUE
            if ($request->apply_to_schedules || in_array($newStatus, ['INACTIVE', 'WEATHER_ISSUE'])) {
                // Map route status to schedule status
                $scheduleStatus = $this->mapRouteStatusToScheduleStatus($newStatus);

                // Get all schedules for this route
                $schedulesQuery = Schedule::where('route_id', $route->id);

                // If time window is specified, filter schedules that operate during that window
                if ($request->start_time && $request->end_time) {
                    $startTime = $request->start_time;
                    $endTime = $request->end_time;

                    $schedulesQuery->where(function ($query) use ($startTime, $endTime) {
                        // Case 1: Schedule departure time falls within the window
                        $query->where(function ($q) use ($startTime, $endTime) {
                            $q->whereTime('departure_time', '>=', $startTime)
                                ->whereTime('departure_time', '<=', $endTime);
                        });

                        // Case 2: Schedule departure time is before start but arrival time is after start
                        $query->orWhere(function ($q) use ($startTime) {
                            $q->whereTime('departure_time', '<', $startTime)
                                ->whereTime('arrival_time', '>', $startTime);
                        });
                    });
                }

                $schedules = $schedulesQuery->get();
                $affectedSchedulesCount = $schedules->count();

                // Calculate the date range for affected days
                $startDate = Carbon::today();
                // Fixed code
                $endDate = $request->affect_days
                    ? Carbon::today()->addDays((int)$request->affect_days)
                    : Carbon::today()->addDay();

                foreach ($schedules as $schedule) {
                    // Update schedule status
                    $schedule->status = $scheduleStatus;
                    $schedule->save();

                    // Update schedule dates based on the date range and status
                    $scheduleDate = null;

                    if ($newStatus === 'WEATHER_ISSUE') {
                        // For weather issues, update dates in the specified range
                        $affected = ScheduleDate::where('schedule_id', $schedule->id)
                            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                            ->update(['status' => 'WEATHER_ISSUE']);

                        $affectedDatesCount += $affected;
                    } elseif ($newStatus === 'INACTIVE') {
                        // For inactive routes, mark future dates as unavailable
                        $affected = ScheduleDate::where('schedule_id', $schedule->id)
                            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                            ->update(['status' => 'UNAVAILABLE']);

                        $affectedDatesCount += $affected;
                    } elseif ($oldStatus !== 'ACTIVE' && $newStatus === 'ACTIVE') {
                        // If returning to active status, set previously affected dates back to available
                        $affected = ScheduleDate::where('schedule_id', $schedule->id)
                            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                            ->whereIn('status', ['WEATHER_ISSUE', 'UNAVAILABLE'])
                            ->update(['status' => 'AVAILABLE']);

                        $affectedDatesCount += $affected;
                    }
                }

                $message = "Status rute diperbarui menjadi {$this->getStatusLabel($newStatus)}.";

                if ($affectedSchedulesCount > 0) {
                    $message .= " {$affectedSchedulesCount} jadwal terkait telah diperbarui.";

                    if ($affectedDatesCount > 0) {
                        $message .= " {$affectedDatesCount} tanggal keberangkatan terpengaruh.";
                    }
                }
            } else {
                $message = "Status rute diperbarui menjadi {$this->getStatusLabel($newStatus)}.";
            }

            DB::commit();

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui status rute: ' . $e->getMessage());
        }
    }

    /**
     * Helper method to map route status to schedule status
     */
    private function mapRouteStatusToScheduleStatus($routeStatus)
    {
        switch ($routeStatus) {
            case 'ACTIVE':
                return 'ACTIVE';
            case 'INACTIVE':
                return 'CANCELLED';
            case 'WEATHER_ISSUE':
                return 'DELAYED';
            default:
                return 'ACTIVE';
        }
    }

    /**
     * Helper method to format status label
     */
    private function getStatusLabel($status)
    {
        switch ($status) {
            case 'ACTIVE':
                return 'Aktif';
            case 'INACTIVE':
                return 'Tidak Aktif';
            case 'WEATHER_ISSUE':
                return 'Masalah Cuaca';
            default:
                return $status;
        }
    }
}
