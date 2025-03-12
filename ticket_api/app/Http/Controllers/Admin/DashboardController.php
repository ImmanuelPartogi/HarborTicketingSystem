<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Models\Ferry;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * @method \Illuminate\Routing\Controller middleware($middleware, array $options = [])
 */
class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get basic stats
        $totalUsers = User::count();
        $totalFerries = Ferry::count();
        $totalRoutes = Route::count();
        $totalSchedules = Schedule::count();

        // Get bookings and revenue stats
        $today = Carbon::today();
        $startOfMonth = Carbon::today()->startOfMonth();
        $startOfWeek = Carbon::today()->startOfWeek();

        $todayBookings = Booking::whereDate('created_at', $today)->count();
        $weekBookings = Booking::whereBetween('created_at', [$startOfWeek, $today])->count();
        $monthBookings = Booking::whereBetween('created_at', [$startOfMonth, $today])->count();

        $todayRevenue = Payment::where('status', 'SUCCESS')
            ->whereDate('payment_date', $today)
            ->sum('amount');

        $weekRevenue = Payment::where('status', 'SUCCESS')
            ->whereBetween('payment_date', [$startOfWeek, $today])
            ->sum('amount');

        $monthRevenue = Payment::where('status', 'SUCCESS')
            ->whereBetween('payment_date', [$startOfMonth, $today])
            ->sum('amount');

        // Get recent bookings
        $recentBookings = Booking::with(['user', 'schedule.route'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get occupancy rate
        $todaySchedules = Schedule::whereHas('scheduleDates', function ($query) use ($today) {
            $query->whereDate('date', $today);
        })->with('scheduleDates', function ($query) use ($today) {
            $query->whereDate('date', $today);
        })->get();

        $occupancyRate = 0;
        $scheduleCount = count($todaySchedules);

        if ($scheduleCount > 0) {
            $totalCapacity = 0;
            $totalPassengers = 0;

            foreach ($todaySchedules as $schedule) {
                $ferry = $schedule->ferry;
                $scheduleDate = $schedule->scheduleDates->first();

                if ($ferry && $scheduleDate) {
                    $totalCapacity += $ferry->capacity_passenger;
                    $totalPassengers += $scheduleDate->passenger_count;
                }
            }

            $occupancyRate = $totalCapacity > 0 ? ($totalPassengers / $totalCapacity) * 100 : 0;
        }

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalFerries',
            'totalRoutes',
            'totalSchedules',
            'todayBookings',
            'weekBookings',
            'monthBookings',
            'todayRevenue',
            'weekRevenue',
            'monthRevenue',
            'recentBookings',
            'occupancyRate'
        ));
    }
}
