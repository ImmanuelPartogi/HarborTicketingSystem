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
use Illuminate\Support\Str;

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
        $dateFilter = $request->query('date');

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

        if ($dateFilter) {
            // If date filter is applied, include schedules that have dates on that day
            $query->whereHas('scheduleDates', function ($q) use ($dateFilter) {
                $q->where('date', $dateFilter);
            });
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

        return view('admin.schedules.index', compact('schedules', 'routes', 'ferries', 'search', 'routeId', 'ferryId', 'status', 'dateFilter'));
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
     * Store a new schedule date with enhanced operation day validation.
     *
     * @param Schedule $schedule
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeDates(Schedule $schedule, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date_type' => 'required|in:single,range,days,multiple',
            'single_date' => 'required_if:date_type,single|date',
            'start_date' => 'required_if:date_type,range|date',
            'end_date' => 'required_if:date_type,range|date|after_or_equal:start_date',
            'days' => 'required_if:date_type,days|array',
            'days.*' => 'integer|between:0,7',
            'days_start_date' => 'required_if:date_type,days|date',
            'days_end_date' => 'required_if:date_type,days|date|after_or_equal:days_start_date',
            'selected_dates' => 'required_if:date_type,multiple|string',
            'status' => 'required|in:AVAILABLE,UNAVAILABLE',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()
                ->with('error', 'Validasi gagal, silakan periksa input Anda.');
        }

        try {
            // Get schedule operation days
            $operationDays = explode(',', $schedule->days);

            // Arrays to track results
            $addedDates = [];
            $skippedDates = [];

            // Handle different date types
            if ($request->date_type === 'single') {
                // Single date mode - validate against operation days
                $date = Carbon::parse($request->single_date);
                $dayOfWeek = $date->dayOfWeekIso; // 1 (Mon) to 7 (Sun)

                if (in_array((string)$dayOfWeek, $operationDays)) {
                    $this->createOrUpdateScheduleDate($schedule, $date->format('Y-m-d'), $request->status);
                    $addedDates[] = $date->format('d/m/Y');
                } else {
                    $skippedDates[] = $date->format('d/m/Y');
                }
            } elseif ($request->date_type === 'range') {
                // Range date mode - only create dates that match operation days
                $startDate = Carbon::parse($request->start_date);
                $endDate = Carbon::parse($request->end_date);
                $currentDate = $startDate->copy();

                while ($currentDate->lte($endDate)) {
                    $dayOfWeek = $currentDate->dayOfWeekIso; // 1 (Mon) to 7 (Sun)

                    if (in_array((string)$dayOfWeek, $operationDays)) {
                        $this->createOrUpdateScheduleDate($schedule, $currentDate->format('Y-m-d'), $request->status);
                        $addedDates[] = $currentDate->format('d/m/Y');
                    } else {
                        $skippedDates[] = $currentDate->format('d/m/Y');
                    }

                    $currentDate->addDay();
                }
            } elseif ($request->date_type === 'days') {
                // Create specific days in date range (original implementation)
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
                        $this->createOrUpdateScheduleDate($schedule, $currentDate->format('Y-m-d'), $request->status);
                        $addedDates[] = $currentDate->format('d/m/Y');
                    } else {
                        $skippedDates[] = $currentDate->format('d/m/Y');
                    }
                    $currentDate->addDay();
                }
            } elseif ($request->date_type === 'multiple') {
                // Multiple date selection mode (new implementation)
                if (!empty($request->selected_dates)) {
                    $selectedDates = explode(',', $request->selected_dates);

                    foreach ($selectedDates as $dateString) {
                        $date = Carbon::parse($dateString);
                        $this->createOrUpdateScheduleDate($schedule, $date->format('Y-m-d'), $request->status);
                        $addedDates[] = $date->format('d/m/Y');
                    }
                }
            }

            // Create success message
            if (count($addedDates) > 0) {
                $message = count($addedDates) . ' jadwal berhasil ditambahkan';

                if (count($addedDates) <= 5) {
                    $message .= ' (' . implode(', ', $addedDates) . ')';
                }

                if (count($skippedDates) > 0) {
                    $message .= '. ' . count($skippedDates) . ' tanggal dilewati karena tidak sesuai hari operasi.';
                }
            } else {
                if (count($skippedDates) > 0) {
                    $message = 'Tidak ada jadwal yang ditambahkan karena semua tanggal tidak sesuai dengan hari operasi kapal (' . implode(', ', $skippedDates) . ').';
                } else {
                    $message = 'Tidak ada jadwal yang ditambahkan.';
                }
            }

            return redirect()->route('admin.schedules.dates', $schedule)
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan tanggal jadwal: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Helper method to create or update a schedule date.
     *
     * @param Schedule $schedule
     * @param string $date
     * @param string $status
     * @return ScheduleDate
     */
    private function createOrUpdateScheduleDate(Schedule $schedule, $date, $status)
    {
        return ScheduleDate::updateOrCreate(
            ['schedule_id' => $schedule->id, 'date' => $date],
            [
                'status' => $status, // Menggunakan parameter $status, bukan $request->status
                'passenger_count' => 0,
                'motorcycle_count' => 0,
                'car_count' => 0,
                'bus_count' => 0,
                'truck_count' => 0
            ]
        );
    }

    /**
     * Update a specific schedule date status.
     *
     * @param Request $request
     * @param Schedule $schedule
     * @param int $dateId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateDate(Request $request, Schedule $schedule, $dateId)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:AVAILABLE,UNAVAILABLE',
            'status_reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Find schedule date by ID
            $scheduleDate = ScheduleDate::findOrFail($dateId);

            // Ensure this schedule date belongs to the correct schedule
            if ($scheduleDate->schedule_id != $schedule->id) {
                return back()->with('error', 'Data jadwal tidak valid');
            }

            // Check if status was changed
            $statusChanged = $request->status != $scheduleDate->status;
            $oldStatus = $scheduleDate->status;

            // Update the status
            $updateData = [
                'status' => $request->status
            ];

            // Add status reason if provided
            if ($request->filled('status_reason')) {
                $updateData['status_reason'] = $request->status_reason;
            }

            // Update the schedule date
            $scheduleDate->update($updateData);

            // Prepare success message
            if ($statusChanged) {
                $oldStatusLabel = $oldStatus == 'AVAILABLE' ? 'Tersedia' : 'Tidak Tersedia';
                $newStatusLabel = $request->status == 'AVAILABLE' ? 'Tersedia' : 'Tidak Tersedia';
                $message = "Status jadwal berhasil diubah dari \"{$oldStatusLabel}\" menjadi \"{$newStatusLabel}\"";
            } else {
                $message = "Status jadwal berhasil diperbarui";
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui status jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Reschedule a delayed schedule (typically due to weather issues).
     * Enhanced to support the more flexible status system.
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
            'affected_dates' => 'required|array',
            'affected_dates.*' => 'date',
            'reschedule_type' => 'required|in:all,selected',
            'status_after_reschedule' => 'required|in:ACTIVE,DELAYED',
            'notification_message' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Save the old schedule details for notification
            $oldDepartureTime = $schedule->departure_time->format('H:i');
            $oldArrivalTime = $schedule->arrival_time->format('H:i');

            // Calculate new arrival time based on route duration
            $newDepartureTime = $request->time;
            $duration = $schedule->route->duration ?? 60; // Duration in minutes, default 60 minutes

            // Parse departure time and add duration to get arrival time
            $departureDateTime = Carbon::createFromFormat('H:i', $newDepartureTime);
            $arrivalDateTime = $departureDateTime->copy()->addMinutes($duration);
            $newArrivalTime = $arrivalDateTime->format('H:i');

            // Store the adjustment details
            $adjustmentId = Str::uuid();
            $adjustmentDetails = [
                'id' => $adjustmentId,
                'schedule_id' => $schedule->id,
                'old_departure' => $oldDepartureTime,
                'old_arrival' => $oldArrivalTime,
                'new_departure' => $newDepartureTime,
                'new_arrival' => $newArrivalTime,
                'new_date' => $request->date,
                'created_at' => now()->toDateTimeString(),
                'notification_message' => $request->notification_message ?? 'Jadwal telah diubah karena alasan operasional.'
            ];

            // Update schedule times and status
            $schedule->update([
                'departure_time' => $newDepartureTime,
                'arrival_time' => $newArrivalTime,
                'status' => $request->status_after_reschedule
            ]);

            // Create new schedule date for the rescheduled date
            $newDate = $request->date;
            $newScheduleDate = ScheduleDate::updateOrCreate(
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

            // Handle affected dates based on reschedule type
            if ($request->reschedule_type === 'all') {
                // Update all weather-affected dates to cancelled
                ScheduleDate::where('schedule_id', $schedule->id)
                    ->where('date', '>=', now()->format('Y-m-d'))
                    ->where('status', 'WEATHER_ISSUE')
                    ->update(['status' => 'CANCELLED']);
            } else {
                // Update only selected dates
                foreach ($request->affected_dates as $affectedDate) {
                    ScheduleDate::where('schedule_id', $schedule->id)
                        ->where('date', $affectedDate)
                        ->update(['status' => 'CANCELLED']);
                }
            }

            // You might want to store the adjustment history in a dedicated table
            // For now, we'll assume there's a method to handle this
            // $this->storeScheduleAdjustment($adjustmentDetails);

            DB::commit();

            // Format dates for display
            $newDateFormatted = Carbon::parse($newDate)->format('d/m/Y');

            return redirect()->route('admin.schedules.index')
                ->with('success', "Jadwal berhasil disesuaikan ke {$newDateFormatted} {$newDepartureTime}. ID penyesuaian: {$adjustmentId}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal melakukan penyesuaian jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Get all schedule dates affected by weather issues for a specific schedule.
     *
     * @param Schedule $schedule
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWeatherAffectedDates(Schedule $schedule)
    {
        $dates = ScheduleDate::where('schedule_id', $schedule->id)
            ->where('date', '>=', now()->format('Y-m-d'))
            ->where('status', 'WEATHER_ISSUE')
            ->orderBy('date')
            ->get()
            ->map(function ($date) {
                return [
                    'id' => $date->id,
                    'date' => $date->date->format('Y-m-d'),
                    'formatted_date' => $date->date->format('d/m/Y')
                ];
            });

        return response()->json(['dates' => $dates]);
    }
}
