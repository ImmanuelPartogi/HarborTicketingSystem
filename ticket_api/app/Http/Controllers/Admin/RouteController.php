<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\ScheduleDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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
     * Update the status of a route.
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
            $route->status_reason = $request->reason ?? null; // Simpan alasan perubahan status
            $route->save();

            // Jika checkbox apply_to_schedules dicentang atau rute berubah ke INACTIVE/WEATHER_ISSUE
            if ($request->apply_to_schedules || in_array($newStatus, ['INACTIVE', 'WEATHER_ISSUE'])) {

                // Map status rute ke status jadwal yang sesuai
                $scheduleStatus = $this->mapRouteStatusToScheduleStatus($newStatus);

                // Update semua jadwal yang menggunakan rute ini
                $schedules = Schedule::where('route_id', $route->id)->get();

                foreach ($schedules as $schedule) {
                    $schedule->status = $scheduleStatus;
                    $schedule->save();

                    // Jika status adalah WEATHER_ISSUE, tandai semua tanggal jadwal yang akan datang
                    if ($newStatus === 'WEATHER_ISSUE') {
                        ScheduleDate::where('schedule_id', $schedule->id)
                            ->where('date', '>=', now()->format('Y-m-d'))
                            ->update(['status' => 'WEATHER_ISSUE']);
                    }

                    // Jika status adalah INACTIVE, tandai semua tanggal jadwal yang akan datang
                    if ($newStatus === 'INACTIVE') {
                        ScheduleDate::where('schedule_id', $schedule->id)
                            ->where('date', '>=', now()->format('Y-m-d'))
                            ->update(['status' => 'UNAVAILABLE']);
                    }

                    // Jika status kembali ACTIVE, tandai tanggal jadwal kembali AVAILABLE
                    if ($oldStatus !== 'ACTIVE' && $newStatus === 'ACTIVE') {
                        ScheduleDate::where('schedule_id', $schedule->id)
                            ->where('date', '>=', now()->format('Y-m-d'))
                            ->where('status', 'in', ['WEATHER_ISSUE', 'UNAVAILABLE'])
                            ->update(['status' => 'AVAILABLE']);
                    }
                }

                // Mencatat jumlah jadwal yang diperbarui
                $affectedSchedulesCount = $schedules->count();
                $message = "Status rute diperbarui menjadi {$this->getStatusLabel($newStatus)}.";

                if ($affectedSchedulesCount > 0) {
                    $message .= " {$affectedSchedulesCount} jadwal terkait telah diperbarui.";
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
     * Tambahkan method pembantu untuk mapping status rute ke status jadwal
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
     * Tambahkan method pembantu untuk format label status
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
