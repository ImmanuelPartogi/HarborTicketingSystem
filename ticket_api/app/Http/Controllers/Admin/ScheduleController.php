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
use Illuminate\Support\Facades\DB;

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
            $query->whereHas('route', function ($q) use ($search) {
                $q->where('origin', 'like', "%{$search}%")
                    ->orWhere('destination', 'like', "%{$search}%");
            })->orWhereHas('ferry', function ($q) use ($search) {
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
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function show(Schedule $schedule, Request $request)
    {
        $schedule->load(['route', 'ferry']);

        // Handle filter
        $month = $request->query('month');
        $year = $request->query('year', date('Y'));

        // Get upcoming schedule dates
        $query = ScheduleDate::where('schedule_id', $schedule->id);

        // Apply filters if provided
        if ($month) {
            $query->whereMonth('date', $month);
        }

        if ($year) {
            $query->whereYear('date', $year);
        } else {
            // Default to upcoming dates if no filters
            $query->where('date', '>=', Carbon::today());
        }

        // Get schedule dates with pagination
        $scheduleDates = $query->orderBy('date')->paginate(10);

        // Get recent bookings for this schedule
        $bookings = $schedule->bookings()
            ->with('scheduleDate')
            ->latest()
            ->take(5)
            ->get();

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
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function dates(Schedule $schedule, Request $request)
    {
        $schedule->load(['route', 'ferry']);

        // Handle filter
        $month = $request->query('month');
        $year = $request->query('year', date('Y'));
        $status = $request->query('status');

        // Build query for schedule dates
        $query = ScheduleDate::where('schedule_id', $schedule->id);

        // Apply filters if provided
        if ($month) {
            $query->whereMonth('date', $month);
        }

        if ($year) {
            $query->whereYear('date', $year);
        }

        if ($status) {
            $query->where('status', $status);
        }

        // Get schedule dates with pagination
        $scheduleDates = $query->orderBy('date')->paginate(15);

        return view('admin.schedules.dates', compact('schedule', 'scheduleDates'));
    }

    /**
     * Store a new schedule date.
     *
     * @param Schedule $schedule
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeDates(Schedule $schedule, Request $request)
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
                    [
                        'status' => $request->status,
                        'passenger_count' => 0,
                        'motorcycle_count' => 0,
                        'car_count' => 0,
                        'bus_count' => 0,
                        'truck_count' => 0
                    ]
                );
            } elseif ($request->date_type === 'range') {
                // Create date range
                $startDate = Carbon::parse($request->start_date);
                $endDate = Carbon::parse($request->end_date);
                $currentDate = $startDate->copy();

                while ($currentDate->lte($endDate)) {
                    ScheduleDate::updateOrCreate(
                        ['schedule_id' => $schedule->id, 'date' => $currentDate->format('Y-m-d')],
                        [
                            'status' => $request->status,
                            'passenger_count' => 0,
                            'motorcycle_count' => 0,
                            'car_count' => 0,
                            'bus_count' => 0,
                            'truck_count' => 0
                        ]
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
                            [
                                'status' => $request->status,
                                'passenger_count' => 0,
                                'motorcycle_count' => 0,
                                'car_count' => 0,
                                'bus_count' => 0,
                                'truck_count' => 0
                            ]
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
     * Update a specific schedule date.
     *
     * @param Request $request
     * @param Schedule $schedule
     * @param string $date
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateDate(Request $request, Schedule $schedule, $dateId)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'status' => 'required|in:AVAILABLE,FULL,CANCELLED,DEPARTED,WEATHER_ISSUE',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Temukan jadwal berdasarkan ID
            $scheduleDate = ScheduleDate::findOrFail($dateId);

            // Pastikan jadwal ini milik schedule yang benar
            if ($scheduleDate->schedule_id != $schedule->id) {
                return back()->with('error', 'Data jadwal tidak valid');
            }

            // Update hanya tanggal dan status
            $scheduleDate->update([
                'date' => $request->date,
                'status' => $request->status,
            ]);

            return back()->with('success', 'Jadwal berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Reschedule a delayed schedule (typically due to weather issues).
     *
     * @param Request $request
     * @param Schedule $schedule
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reschedule(Request $request, Schedule $schedule)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Simpan waktu keberangkatan lama untuk notifikasi
            $oldDepartureTime = $schedule->departure_time->format('H:i');
            $oldDate = now()->format('d/m/Y');

            // Hitung waktu tiba baru berdasarkan durasi perjalanan
            $newDepartureTime = $request->time;
            $duration = $schedule->route->duration ?? 60; // durasi dalam menit, default 60 menit

            // Parse waktu keberangkatan dan tambahkan durasi untuk mendapatkan waktu tiba
            $departureDateTime = Carbon::createFromFormat('H:i', $newDepartureTime);
            $arrivalDateTime = $departureDateTime->copy()->addMinutes($duration);
            $newArrivalTime = $arrivalDateTime->format('H:i');

            // Update jadwal jika rute masih dalam status WEATHER_ISSUE
            if ($schedule->route->status === 'WEATHER_ISSUE') {
                // Jadwal tetap DELAYED, tapi dengan waktu yang baru
                // Pengguna bisa mengubah route ke ACTIVE nanti jika masalah cuaca sudah selesai
                $schedule->update([
                    'departure_time' => $newDepartureTime,
                    'arrival_time' => $newArrivalTime,
                ]);
            } else {
                // Jika status rute sudah berubah (misalnya kembali ACTIVE),
                // maka jadwal juga bisa diubah ke ACTIVE
                $schedule->update([
                    'departure_time' => $newDepartureTime,
                    'arrival_time' => $newArrivalTime,
                    'status' => 'ACTIVE'
                ]);
            }

            // Buat tanggal jadwal baru untuk tanggal reschedule
            $newDate = $request->date;
            ScheduleDate::updateOrCreate(
                ['schedule_id' => $schedule->id, 'date' => $newDate],
                [
                    'status' => 'AVAILABLE',
                    'passenger_count' => 0,
                    'motorcycle_count' => 0,
                    'car_count' => 0,
                    'bus_count' => 0,
                    'truck_count' => 0
                ]
            );

            DB::commit();

            // Format tanggal dan waktu baru untuk ditampilkan
            $newDateFormatted = Carbon::parse($newDate)->format('d/m/Y');

            return redirect()->route('admin.schedules.index')
                ->with('success', "Jadwal berhasil di-reschedule dari {$oldDate} {$oldDepartureTime} ke {$newDateFormatted} {$newDepartureTime}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal melakukan reschedule: ' . $e->getMessage());
        }
    }
}
