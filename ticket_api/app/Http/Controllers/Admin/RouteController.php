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
use Illuminate\Validation\Rule;

/**
 * RouteController - Manages ferry routes in the admin panel
 *
 * This controller handles CRUD operations for routes and their status updates,
 * which can affect related schedules and schedule dates.
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
     * Display a listing of routes with filtering options.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $query = Route::query();

        // Apply search filter if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('origin', 'like', "%{$search}%")
                    ->orWhere('destination', 'like', "%{$search}%");
            });
        }

        // Apply status filter if provided
        if ($status) {
            $query->where('status', $status);
        }

        // Get routes with pagination
        $routes = $query->orderBy('origin')
            ->orderBy('destination')
            ->paginate(15)
            ->withQueryString(); // Maintain query parameters across pagination

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
            'status' => ['required', Rule::in(['ACTIVE', 'INACTIVE', 'WEATHER_ISSUE'])],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if route already exists with same origin and destination
        $existingRoute = Route::where('origin', $request->origin)
            ->where('destination', $request->destination)
            ->first();

        if ($existingRoute) {
            return back()->withInput()
                ->with('error', 'Route already exists with the same origin and destination');
        }

        try {
            DB::beginTransaction();

            $route = Route::create($request->all());

            DB::commit();

            return redirect()->route('admin.routes.index')
                ->with('success', 'Route created successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error for debugging
            logger()->error('Failed to create route: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', 'Failed to create route. Please try again later.');
        }
    }

    /**
     * Display the specified route with its schedules.
     *
     * @param Route $route
     * @return \Illuminate\View\View
     */
    public function show(Route $route)
    {
        // Eager load ferry relationship to avoid N+1 query problem
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
            'status' => ['required', Rule::in(['ACTIVE', 'INACTIVE', 'WEATHER_ISSUE'])],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if route already exists with same origin and destination (excluding current route)
        $existingRoute = Route::where('origin', $request->origin)
            ->where('destination', $request->destination)
            ->where('id', '!=', $route->id)
            ->first();

        if ($existingRoute) {
            return back()->withInput()
                ->with('error', 'Route already exists with the same origin and destination');
        }

        try {
            DB::beginTransaction();

            $route->update($request->all());

            DB::commit();

            return redirect()->route('admin.routes.index')
                ->with('success', 'Route updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error for debugging
            logger()->error('Failed to update route: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', 'Failed to update route. Please try again later.');
        }
    }

    /**
     * Remove the specified route if it has no associated schedules.
     *
     * @param Route $route
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Route $route)
    {
        try {
            // Check if route has any schedules
            $hasSchedules = Schedule::where('route_id', $route->id)->exists();

            if ($hasSchedules) {
                return back()->with('error', 'Cannot delete route with existing schedules');
            }

            DB::beginTransaction();

            $route->delete();

            DB::commit();

            return redirect()->route('admin.routes.index')
                ->with('success', 'Route deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error for debugging
            logger()->error('Failed to delete route: ' . $e->getMessage());

            return back()->with('error', 'Failed to delete route. Please try again later.');
        }
    }

    /**
     * Update the status of a route with a flexible approach.
     * This method can also update related schedules and schedule dates.
     *
     * @param Request $request
     * @param Route $route
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, Route $route)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(['ACTIVE', 'INACTIVE', 'WEATHER_ISSUE'])],
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

            // Store previous status for comparison
            $oldStatus = $route->status;
            $newStatus = $request->status;

            // Update route status
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
                $endDate = $request->affect_days
                    ? Carbon::today()->addDays((int)$request->affect_days)
                    : Carbon::today()->addDay();

                foreach ($schedules as $schedule) {
                    // Update schedule status
                    $schedule->status = $scheduleStatus;
                    $schedule->save();

                    // Update schedule dates based on the date range and status
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

                // Prepare success message
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

            // Log the error for debugging
            logger()->error('Failed to update route status: ' . $e->getMessage());

            return back()->with('error', 'Gagal memperbarui status rute. Silakan coba lagi nanti.');
        }
    }

    /**
     * Helper method to map route status to schedule status
     *
     * @param string $routeStatus
     * @return string
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
     * Helper method to format status label in Indonesian
     *
     * @param string $status
     * @return string
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
