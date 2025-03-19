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
use Illuminate\Support\Facades\Schema;

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
     * Update the status of a route without affecting related schedules.
     *
     * @param Request $request
     * @param Route $route
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, Route $route)
    {
        try {
            DB::beginTransaction();

            // Store previous status for comparison
            $oldStatus = $route->status;
            $newStatus = $request->status;

            // Update route status
            $route->status = $newStatus;
            $route->status_reason = $request->reason ?? null;

            // If status_updated_at column exists, update it
            if (Schema::hasColumn('routes', 'status_updated_at')) {
                $route->status_updated_at = now();
            }

            // If status_expiry_date column exists and status is WEATHER_ISSUE
            if (Schema::hasColumn('routes', 'status_expiry_date') && $newStatus === 'WEATHER_ISSUE') {
                $daysToExpire = (int)($request->affect_days ?? 3);
                $route->status_expiry_date = Carbon::now()->addDays($daysToExpire);
            } elseif (Schema::hasColumn('routes', 'status_expiry_date')) {
                $route->status_expiry_date = null;
            }

            $route->save();

            DB::commit();

            return back()->with('success', "Status rute diperbarui menjadi {$this->getStatusLabel($newStatus)}.");
        } catch (\Exception $e) {
            DB::rollBack();

            // Log the detailed error for debugging
            logger()->error('Failed to update route status: ' . $e->getMessage());

            // Return a user-friendly error message with detail (for dev environment)
            return back()->with('error', 'Gagal memperbarui status rute: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update schedule dates based on route status changes with appropriate rules.
     * This handles various status transition scenarios and respects final statuses.
     *
     * @param Schedule $schedule
     * @param string $newRouteStatus
     * @param string $oldRouteStatus
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param Carbon|null $expiryDate
     * @return int Number of affected dates
     */
    private function updateScheduleDates(Schedule $schedule, $newRouteStatus, $oldRouteStatus, $startDate, $endDate, $expiryDate = null)
    {
        $affectedDates = 0;

        // Query to get dates that can be modified (exclude final states)
        $query = ScheduleDate::where('schedule_id', $schedule->id)
            ->where('date', '>=', $startDate->format('Y-m-d'))
            ->whereNotIn('status', ['FULL', 'DEPARTED']); // Don't modify dates with final status

        if ($newRouteStatus === 'WEATHER_ISSUE') {
            // For weather issues, only update dates within the specified range
            $query->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

            // Update to WEATHER_ISSUE status with expiry date
            $affected = $query->update([
                'status' => 'WEATHER_ISSUE',
                'status_expiry_date' => $expiryDate,
                'status_reason' => 'Perubahan status karena masalah cuaca pada rute',
                'modified_by_route' => true // Mark as modified by route
            ]);

            $affectedDates += $affected;
        } elseif ($newRouteStatus === 'INACTIVE') {
            // For inactive routes, mark all future dates as UNAVAILABLE
            $affected = $query->update([
                'status' => 'UNAVAILABLE',
                'status_reason' => 'Rute tidak aktif',
                'modified_by_route' => true // Mark as modified by route
            ]);

            $affectedDates += $affected;
        } elseif ($oldRouteStatus !== 'ACTIVE' && $newRouteStatus === 'ACTIVE') {
            // If returning to active status from a non-active state
            // Only reset dates that were previously modified by route changes
            $affected = $query
                ->where('modified_by_route', true)
                ->whereIn('status', ['WEATHER_ISSUE', 'UNAVAILABLE'])
                ->update([
                    'status' => 'AVAILABLE',
                    'status_expiry_date' => null,
                    'status_reason' => null,
                    'modified_by_route' => false // Reset this flag
                ]);

            $affectedDates += $affected;
        }

        return $affectedDates;
    }

    /**
     * Process schedule dates based on departure time.
     * Changes FULL schedule dates to DEPARTED status when departure time is reached.
     * This should be called by a scheduled command.
     *
     * @return array Status counts
     */
    public function processScheduleDatesByTime()
    {
        $now = Carbon::now();
        $processedCount = 0;

        try {
            // Find all FULL schedule dates for today
            $scheduleDates = ScheduleDate::with('schedule')
                ->where('status', 'FULL')
                ->whereDate('date', $now->format('Y-m-d'))
                ->get();

            foreach ($scheduleDates as $scheduleDate) {
                if ($scheduleDate->schedule) {
                    // Get departure time and combine with date
                    $departureTime = $scheduleDate->schedule->departure_time;
                    $departureDateTime = Carbon::parse(
                        $scheduleDate->date->format('Y-m-d') . ' ' . $departureTime->format('H:i:s')
                    );

                    // If departure time has passed, mark as DEPARTED
                    if ($now->gt($departureDateTime)) {
                        $scheduleDate->status = 'DEPARTED';
                        $scheduleDate->save();
                        $processedCount++;
                    }
                }
            }

            return [
                'success' => true,
                'processed' => $processedCount
            ];
        } catch (\Exception $e) {
            logger()->error('Failed to process schedule dates by time: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
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
