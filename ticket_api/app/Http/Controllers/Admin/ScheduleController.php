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
use Illuminate\Support\Facades\Log;
use App\Models\Booking;

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
        // Get only active routes for selection
        $routes = Route::where('status', 'ACTIVE')
            ->orderBy('origin')
            ->orderBy('destination')
            ->get();

        // Get only active ferries for selection
        $ferries = Ferry::where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();

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
     * Update the specified schedule without price fields.
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
            'days.*' => 'required|in:1,2,3,4,5,6,7,0',
            'status' => 'required|in:ACTIVE,CANCELLED',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Format days as comma-separated string
            $days = implode(',', $request->days);

            // Store old status for comparison
            $oldStatus = $schedule->status;
            $newStatus = $request->status;

            // Check if the schedule has a final status
            if (Schedule::isStatusFinal($oldStatus)) {
                // If it's a final status, ignore status change
                $newStatus = $oldStatus;
            }

            // Update schedule without price fields (removed)
            $schedule->update([
                'route_id' => $request->route_id,
                'ferry_id' => $request->ferry_id,
                'departure_time' => $request->departure_time,
                'arrival_time' => $request->arrival_time,
                'days' => $days,
                'status' => $newStatus,
            ]);

            // If status has changed and is not final, update related schedule dates
            if ($oldStatus !== $newStatus && !Schedule::isStatusFinal($oldStatus)) {
                $this->updateScheduleDatesByScheduleStatus($schedule, $newStatus);
            }

            DB::commit();

            return redirect()->route('admin.schedules.index')
                ->with('success', 'Jadwal berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update schedule: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal memperbarui jadwal: ' . $e->getMessage());
        }
    }


    /**
     * Update schedule dates based on simplified schedule status.
     * Only updates dates that are not in final status (FULL or DEPARTED).
     *
     * @param Schedule $schedule
     * @param string $newStatus
     * @return int Number of affected dates
     */
    private function updateScheduleDatesByScheduleStatus(Schedule $schedule, $newStatus)
    {
        // For inactive schedules, mark dates as unavailable
        if ($newStatus === 'CANCELLED') {
            // Update only future dates that are not in final status
            return ScheduleDate::where('schedule_id', $schedule->id)
                ->where('date', '>=', now()->format('Y-m-d'))
                ->whereNotIn('status', ['FULL', 'DEPARTED']) // Don't update final statuses
                ->update([
                    'status' => 'UNAVAILABLE',
                    'status_reason' => 'Jadwal tidak aktif'
                    // Removed 'modified_by_schedule' column reference
                ]);
        }
        // For active schedules, reset dates to available
        elseif ($newStatus === 'ACTIVE') {
            // Update future dates that are not in final status
            return ScheduleDate::where('schedule_id', $schedule->id)
                ->where('date', '>=', now()->format('Y-m-d'))
                ->whereIn('status', ['UNAVAILABLE']) // Only update unavailable dates
                ->whereNotIn('status', ['FULL', 'DEPARTED'])
                ->update([
                    'status' => 'AVAILABLE',
                    'status_reason' => null
                    // Removed 'modified_by_schedule' column reference
                ]);
        }

        return 0;
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
     * Delete a specific schedule date.
     *
     * @param int $dateId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteDate($dateId)
    {
        try {
            // Find the schedule date
            $scheduleDate = ScheduleDate::findOrFail($dateId);

            // Get the schedule ID for redirect
            $scheduleId = $scheduleDate->schedule_id;

            // Get the date of this schedule date
            $dateValue = $scheduleDate->date;

            // Check if this date has any bookings
            // We're assuming bookings have a travel_date and are linked to the schedule
            $hasBookings = Booking::where('schedule_id', $scheduleId)
                ->exists();

            if ($hasBookings) {
                return back()->with('error', 'Tidak dapat menghapus tanggal yang memiliki pemesanan.');
            }

            // Check if status is final (FULL or DEPARTED)
            if (in_array($scheduleDate->status, ['FULL', 'DEPARTED'])) {
                return back()->with('error', 'Tidak dapat menghapus tanggal dengan status final (Penuh atau Selesai).');
            }

            // Delete the schedule date
            $scheduleDate->delete();

            return redirect()->route('admin.schedules.dates', $scheduleId)
                ->with('success', 'Tanggal jadwal berhasil dihapus.');
        } catch (\Exception $e) {
            // Log error
            Log::error('Failed to delete schedule date: ' . $e->getMessage());

            return back()->with('error', 'Gagal menghapus tanggal jadwal: ' . $e->getMessage());
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
     * Store a new schedule date with enhanced operation day validation and debug logging.
     *
     * @param Schedule $schedule
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeDates(Schedule $schedule, Request $request)
    {
        // Log semua data request untuk debug
        Log::info('Schedule Date Store Request', [
            'schedule_id' => $schedule->id,
            'all_data' => $request->all()
        ]);

        // Perbaikan: Validasi yang lebih jelas dan menampilkan pesan lebih informatif
        $validator = Validator::make($request->all(), [
            'date_type' => 'required|in:single,range,days,multiple',
            'single_date' => 'required_if:date_type,single|date',
            'start_date' => 'required_if:date_type,range|date',
            'end_date' => 'required_if:date_type,range|date|after_or_equal:start_date',
            'days' => 'required_if:date_type,days|array',
            'days.*' => 'required_if:date_type,days|integer|between:1,7',
            'days_start_date' => 'required_if:date_type,days|date',
            'days_end_date' => 'required_if:date_type,days|date|after_or_equal:days_start_date',
            'selected_dates' => 'required_if:date_type,multiple',
            'status' => 'required|in:AVAILABLE,UNAVAILABLE',
        ], [
            'date_type.required' => 'Tipe penambahan tanggal harus dipilih',
            'date_type.in' => 'Tipe penambahan tanggal tidak valid',
            'single_date.required_if' => 'Tanggal harus diisi untuk tipe tanggal tunggal',
            'start_date.required_if' => 'Tanggal mulai harus diisi untuk tipe rentang tanggal',
            'end_date.required_if' => 'Tanggal akhir harus diisi untuk tipe rentang tanggal',
            'end_date.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal mulai',
            'days.required_if' => 'Minimal satu hari harus dipilih untuk tipe hari tertentu',
            'days_start_date.required_if' => 'Tanggal mulai harus diisi untuk tipe hari tertentu',
            'days_end_date.required_if' => 'Tanggal akhir harus diisi untuk tipe hari tertentu',
            'days_end_date.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal mulai',
            'selected_dates.required_if' => 'Minimal satu tanggal harus dipilih untuk tipe pilih beberapa tanggal',
            'status.required' => 'Status jadwal harus dipilih',
            'status.in' => 'Status jadwal tidak valid'
        ]);

        if ($validator->fails()) {
            // Log kegagalan validasi untuk debug
            Log::warning('Schedule Date Validation Failed', [
                'schedule_id' => $schedule->id,
                'errors' => $validator->errors()->toArray()
            ]);

            return back()->withErrors($validator)->withInput()
                ->with('error', 'Validasi gagal: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            // Mendapatkan hari operasi jadwal
            $operationDays = explode(',', $schedule->days);
            Log::info('Operation days', ['days' => $operationDays]);

            // Array untuk pelacakan hasil
            $addedDates = [];
            $skippedDates = [];

            // Tangani berbagai tipe tanggal
            if ($request->date_type === 'single') {
                Log::info('Processing single date mode', ['date' => $request->single_date]);

                // Mode tanggal tunggal - validasi terhadap hari operasi
                $date = Carbon::parse($request->single_date);
                $dayOfWeek = $date->dayOfWeekIso; // 1 (Sen) hingga 7 (Min)

                Log::info('Single date day check', [
                    'date' => $date->format('Y-m-d'),
                    'dayOfWeek' => $dayOfWeek,
                    'operationDays' => $operationDays,
                    'isValid' => in_array((string)$dayOfWeek, $operationDays)
                ]);

                if (in_array((string)$dayOfWeek, $operationDays)) {
                    $this->createOrUpdateScheduleDate($schedule, $date->format('Y-m-d'), $request->status);
                    $addedDates[] = $date->format('d/m/Y');
                } else {
                    $skippedDates[] = $date->format('d/m/Y');
                }
            } elseif ($request->date_type === 'range') {
                Log::info('Processing range date mode', [
                    'start' => $request->start_date,
                    'end' => $request->end_date
                ]);

                // Mode rentang tanggal - hanya membuat tanggal yang sesuai hari operasi
                $startDate = Carbon::parse($request->start_date);
                $endDate = Carbon::parse($request->end_date);
                $currentDate = $startDate->copy();

                while ($currentDate->lte($endDate)) {
                    $dayOfWeek = $currentDate->dayOfWeekIso; // 1 (Sen) hingga 7 (Min)

                    Log::info('Range date day check', [
                        'date' => $currentDate->format('Y-m-d'),
                        'dayOfWeek' => $dayOfWeek,
                        'isValid' => in_array((string)$dayOfWeek, $operationDays)
                    ]);

                    if (in_array((string)$dayOfWeek, $operationDays)) {
                        $this->createOrUpdateScheduleDate($schedule, $currentDate->format('Y-m-d'), $request->status);
                        $addedDates[] = $currentDate->format('d/m/Y');
                    } else {
                        $skippedDates[] = $currentDate->format('d/m/Y');
                    }

                    $currentDate->addDay();
                }
            } elseif ($request->date_type === 'days') {
                Log::info('Processing days mode', [
                    'selectedDays' => $request->days,
                    'start' => $request->days_start_date,
                    'end' => $request->days_end_date
                ]);

                // Buat tanggal spesifik dalam rentang tanggal
                $startDate = Carbon::parse($request->days_start_date);
                $endDate = Carbon::parse($request->days_end_date);
                $currentDate = $startDate->copy();

                $selectedDays = $request->days;

                while ($currentDate->lte($endDate)) {
                    $dayOfWeek = $currentDate->dayOfWeekIso; // 1 (Sen) hingga 7 (Min)

                    Log::info('Days mode check', [
                        'date' => $currentDate->format('Y-m-d'),
                        'dayOfWeek' => $dayOfWeek,
                        'selectedDays' => $selectedDays,
                        'isSelected' => in_array($dayOfWeek, $selectedDays)
                    ]);

                    if (in_array($dayOfWeek, $selectedDays)) {
                        $this->createOrUpdateScheduleDate($schedule, $currentDate->format('Y-m-d'), $request->status);
                        $addedDates[] = $currentDate->format('d/m/Y');
                    } else {
                        $skippedDates[] = $currentDate->format('d/m/Y');
                    }
                    $currentDate->addDay();
                }
            } elseif ($request->date_type === 'multiple') {
                Log::info('Processing multiple date mode', ['selectedDates' => $request->selected_dates]);

                // Mode pilihan tanggal berganda
                if (!empty($request->selected_dates)) {
                    $selectedDates = explode(',', $request->selected_dates);

                    foreach ($selectedDates as $dateString) {
                        Log::info('Processing multiple date', ['date' => $dateString]);

                        $date = Carbon::parse($dateString);
                        $this->createOrUpdateScheduleDate($schedule, $date->format('Y-m-d'), $request->status);
                        $addedDates[] = $date->format('d/m/Y');
                    }
                }
            }

            // Buat pesan sukses
            if (count($addedDates) > 0) {
                $message = count($addedDates) . ' jadwal berhasil ditambahkan';

                if (count($addedDates) <= 5) {
                    $message .= ' (' . implode(', ', $addedDates) . ')';
                }

                if (count($skippedDates) > 0) {
                    $message .= '. ' . count($skippedDates) . ' tanggal dilewati karena tidak sesuai hari operasi.';
                }

                Log::info('Schedule dates added successfully', [
                    'schedule_id' => $schedule->id,
                    'added_count' => count($addedDates),
                    'skipped_count' => count($skippedDates)
                ]);
            } else {
                if (count($skippedDates) > 0) {
                    $message = 'Tidak ada jadwal yang ditambahkan karena semua tanggal tidak sesuai dengan hari operasi kapal (' . implode(', ', $skippedDates) . ').';
                } else {
                    $message = 'Tidak ada jadwal yang ditambahkan.';
                }

                Log::warning('No schedule dates added', [
                    'schedule_id' => $schedule->id,
                    'skipped_dates' => $skippedDates
                ]);
            }

            return redirect()->route('admin.schedules.dates', $schedule)
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to add schedule dates', [
                'schedule_id' => $schedule->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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
        Log::info('Creating/updating schedule date', [
            'schedule_id' => $schedule->id,
            'date' => $date,
            'status' => $status
        ]);

        return ScheduleDate::updateOrCreate(
            ['schedule_id' => $schedule->id, 'date' => $date],
            [
                'status' => $status,
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
     * Implements business rules for status editing that respect final statuses.
     *
     * @param Request $request
     * @param Schedule $schedule
     * @param int $dateId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateDate(Request $request, Schedule $schedule, $dateId)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:AVAILABLE,UNAVAILABLE,WEATHER_ISSUE,FULL,DEPARTED',
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

            // Check if current status is final
            $isCurrentStatusFinal = in_array($scheduleDate->status, ['FULL', 'DEPARTED']);

            // Check if new status is final
            $isNewStatusFinal = in_array($request->status, ['FULL', 'DEPARTED']);

            // If current status is final, prevent changes
            if ($isCurrentStatusFinal) {
                return back()->with('error', 'Status jadwal tidak dapat diubah karena sudah berstatus final.');
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
            } else {
                $updateData['status_reason'] = null; // Clear reason if not provided
            }

            // If changing to final status, add a warning
            if ($isNewStatusFinal) {
                $updateData['modified_by_schedule'] = false; // Prevent auto-updates
            }

            // Update the schedule date
            $scheduleDate->update($updateData);

            // Prepare success message
            if ($statusChanged) {
                $oldStatusLabel = $this->getStatusLabel($oldStatus);
                $newStatusLabel = $this->getStatusLabel($request->status);

                $message = "Status jadwal berhasil diubah dari \"{$oldStatusLabel}\" menjadi \"{$newStatusLabel}\"";

                if ($isNewStatusFinal) {
                    $message .= ". Perhatian: Status ini adalah status final dan tidak dapat diubah kembali.";
                }
            } else {
                $message = "Status jadwal berhasil diperbarui";
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            // Log error detail
            logger()->error('Failed to update schedule date: ' . $e->getMessage());

            return back()->with('error', 'Gagal memperbarui status jadwal: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Helper function to get a human-readable status label
     *
     * @param string $status
     * @return string
     */
    private function getStatusLabel($status)
    {
        switch ($status) {
            case 'AVAILABLE':
                return 'Tersedia';
            case 'UNAVAILABLE':
                return 'Tidak Tersedia';
            case 'WEATHER_ISSUE':
                return 'Masalah Cuaca';
            case 'FULL':
                return 'Penuh';
            case 'DEPARTED':
                return 'Selesai';
            default:
                return $status;
        }
    }

    /**
     * Reschedule a schedule (typically due to weather issues).
     * Enhanced to work with the new independent status system.
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
     * Process schedule dates based on status and time.
     * Changes FULL schedule dates to DEPARTED status when arrival time has passed.
     * This should be called by a scheduled command.
     *
     * @return array Status counts
     */
    public function processScheduleDatesByStatus()
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
                    // Get arrival time and combine with date
                    $arrivalTime = $scheduleDate->schedule->arrival_time;
                    $arrivalDateTime = Carbon::parse(
                        $scheduleDate->date->format('Y-m-d') . ' ' . $arrivalTime->format('H:i:s')
                    );

                    // If arrival time has passed, mark as DEPARTED
                    if ($now->gt($arrivalDateTime)) {
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
            logger()->error('Failed to process schedule dates by status: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
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

    /**
     * Get all affected dates for a specific schedule.
     * This includes all dates with WEATHER_ISSUE or UNAVAILABLE status.
     *
     * @param Schedule $schedule
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function getAffectedDates(Schedule $schedule, Request $request)
    {
        // Get dates with WEATHER_ISSUE or UNAVAILABLE status
        $affectedDates = ScheduleDate::where('schedule_id', $schedule->id)
            ->where('date', '>=', now()->format('Y-m-d'))
            ->whereIn('status', ['WEATHER_ISSUE', 'UNAVAILABLE'])
            ->orderBy('date')
            ->get();

        // If request is AJAX, return JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'dates' => $affectedDates->map(function ($date) {
                    return [
                        'id' => $date->id,
                        'date' => $date->date->format('Y-m-d'),
                        'formatted_date' => $date->date->format('d/m/Y'),
                        'status' => $date->status,
                        'status_label' => $date->getStatusLabelAttribute(),
                        'passenger_count' => $date->passenger_count
                    ];
                })
            ]);
        }

        // For regular request, return view with data
        return view('admin.schedules.reschedule', [
            'schedule' => $schedule,
            'affectedDates' => $affectedDates
        ]);
    }

    /**
     * Show reschedule form for a schedule.
     *
     * @param Schedule $schedule
     * @return \Illuminate\View\View
     */
    public function showRescheduleForm(Schedule $schedule)
    {
        // Get dates with WEATHER_ISSUE or UNAVAILABLE status
        $affectedDates = ScheduleDate::where('schedule_id', $schedule->id)
            ->where('date', '>=', now()->format('Y-m-d'))
            ->whereIn('status', ['WEATHER_ISSUE', 'UNAVAILABLE'])
            ->orderBy('date')
            ->get();

        return view('admin.schedules.reschedule', [
            'schedule' => $schedule,
            'affectedDates' => $affectedDates
        ]);
    }
}
