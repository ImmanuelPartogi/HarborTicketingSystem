<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Route;
use App\Models\Ferry;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DailyReportExport;
use App\Exports\MonthlyReportExport;
use App\Exports\RouteReportExport;
use App\Exports\OccupancyReportExport;

/**
 * @method \Illuminate\Routing\Controller middleware($middleware, array $options = [])
 */
class ReportController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Show daily report.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function daily(Request $request)
    {
        $date = $request->query('date') ? Carbon::parse($request->query('date')) : Carbon::today();

        // Get bookings made on the specified date
        $bookings = Booking::whereDate('bookings.created_at', $date) // Tambahkan prefix 'bookings.'
            ->with(['user', 'schedule.route'])
            ->orderBy('bookings.created_at') // Tambahkan prefix 'bookings.'
            ->get();

        // Get total amount from successful payments made on the specified date
        $revenue = Payment::where('status', 'SUCCESS')
            ->whereDate('payment_date', $date)
            ->sum('amount');

        // Get total passenger count and vehicle count for the date
        $passengerCount = $bookings->sum('passenger_count');
        $vehicleCount = $bookings->sum('vehicle_count');

        // Get payment method stats
        $paymentMethods = Payment::where('status', 'SUCCESS')
            ->whereDate('payment_date', $date)
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get();

        // Tambahkan data untuk tampilan
        $totalBookings = $bookings->count();
        $totalRevenue = $revenue;
        $totalPassengers = $passengerCount;
        $totalVehicles = $vehicleCount;

        // Data statistik per jam
        $hourlyStats = [];
        for ($i = 0; $i < 24; $i++) {
            $hourlyStats[$i] = [
                'bookings' => 0,
                'passengers' => 0,
                'revenue' => 0
            ];
        }

        foreach ($bookings as $booking) {
            $hour = $booking->created_at->hour;
            $hourlyStats[$hour]['bookings']++;
            $hourlyStats[$hour]['passengers'] += $booking->passenger_count;
            $hourlyStats[$hour]['revenue'] += $booking->total_amount;
        }

        // Data statistik per rute
        $routeStats = [];
        foreach ($bookings as $booking) {
            $routeName = $booking->schedule->route->origin . ' - ' . $booking->schedule->route->destination;

            if (!isset($routeStats[$routeName])) {
                $routeStats[$routeName] = [
                    'bookings' => 0,
                    'passengers' => 0,
                    'revenue' => 0
                ];
            }

            $routeStats[$routeName]['bookings']++;
            $routeStats[$routeName]['passengers'] += $booking->passenger_count;
            $routeStats[$routeName]['revenue'] += $booking->total_amount;
        }

        return view('admin.reports.daily', compact(
            'date',
            'bookings',
            'revenue',
            'passengerCount',
            'vehicleCount',
            'paymentMethods',
            'totalBookings',
            'totalRevenue',
            'totalPassengers',
            'totalVehicles',
            'hourlyStats',
            'routeStats'
        ));
    }

    /**
     * Show monthly report.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function monthly(Request $request)
    {
        $year = $request->query('year') ? (int)$request->query('year') : Carbon::now()->year;
        $month = $request->query('month') ? (int)$request->query('month') : Carbon::now()->month;

        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Get daily revenue data for the month
        $dailyRevenue = Payment::where('status', 'SUCCESS')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get total revenue for the month
        $totalRevenue = $dailyRevenue->sum('total');

        // Get bookings count for the month
        $bookingsCount = Booking::whereBetween('bookings.created_at', [$startDate, $endDate])->count(); // Tambahkan prefix 'bookings.'

        // Get passenger count for the month
        $passengerCount = Booking::whereBetween('bookings.created_at', [$startDate, $endDate])->sum('passenger_count'); // Tambahkan prefix 'bookings.'

        // Get route statistics
        $routeStats = Booking::whereBetween('bookings.created_at', [$startDate, $endDate->format('Y-m-d') . ' 23:59:59'])
            ->join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
            ->join('routes', 'schedules.route_id', '=', 'routes.id')
            ->selectRaw('routes.id, routes.origin, routes.destination, COUNT(*) as booking_count, SUM(bookings.passenger_count) as passenger_count, SUM(bookings.total_amount) as revenue')
            ->groupBy('routes.id', 'routes.origin', 'routes.destination')
            ->orderByDesc('booking_count')
            ->get();

        return view('admin.reports.monthly', compact(
            'year',
            'month',
            'dailyRevenue',
            'totalRevenue',
            'bookingsCount',
            'passengerCount',
            'routeStats'
        ));
    }

    /**
     * Show route performance report.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function routes(Request $request)
    {
        $startDate = $request->query('date_from') ? Carbon::parse($request->query('date_from')) : Carbon::now()->startOfMonth();
        $endDate = $request->query('date_to') ? Carbon::parse($request->query('date_to')) : Carbon::now()->endOfMonth();

        // Get route performance statistics
        $routeStats = Booking::whereBetween('bookings.created_at', [$startDate, $endDate->format('Y-m-d') . ' 23:59:59'])
            ->join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
            ->join('routes', 'schedules.route_id', '=', 'routes.id')
            ->selectRaw('routes.id, routes.origin, routes.destination, COUNT(*) as booking_count, SUM(bookings.passenger_count) as passenger_count, SUM(bookings.total_amount) as revenue')
            ->groupBy('routes.id', 'routes.origin', 'routes.destination')
            ->orderByDesc('booking_count')
            ->get();

        return view('admin.reports.routes', compact('routeStats', 'startDate', 'endDate'));
    }

    /**
     * Show ferry occupancy report.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function occupancy(Request $request)
    {
        $startDate = $request->query('date_from') ? Carbon::parse($request->query('date_from')) : Carbon::now()->startOfMonth();
        $endDate = $request->query('date_to') ? Carbon::parse($request->query('date_to')) : Carbon::now()->endOfMonth();
        $ferryId = $request->query('ferry_id');

        $ferries = Ferry::all();

        // Query dasar untuk data okupansi
        $query = Booking::whereBetween('bookings.created_at', [$startDate, $endDate->format('Y-m-d') . ' 23:59:59'])
            ->join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
            ->join('ferries', 'schedules.ferry_id', '=', 'ferries.id')
            ->join('routes', 'schedules.route_id', '=', 'routes.id');

        // Filter berdasarkan ferry jika ada
        if ($ferryId) {
            $query->where('ferries.id', $ferryId);
        }

        // Lanjutkan query dengan select dan group by
        // Menggunakan nama kolom yang benar: capacity_passenger (bukan passenger_capacity)
        $occupancyData = $query->selectRaw('
            ferries.name as ferry_name,
            routes.origin,
            routes.destination,
            COUNT(DISTINCT schedules.id) as trip_count,
            SUM(ferries.capacity_passenger) as total_capacity,
            SUM(bookings.passenger_count) as total_passengers,
            (SUM(bookings.passenger_count) / SUM(ferries.capacity_passenger)) * 100 as occupancy_rate
        ')
            ->groupBy('ferries.id', 'ferries.name', 'routes.id', 'routes.origin', 'routes.destination')
            ->orderByDesc('occupancy_rate')
            ->get();

        return view('admin.reports.occupancy', compact('occupancyData', 'ferries', 'startDate', 'endDate', 'ferryId'));
    }

    /**
     * Export daily report to Excel.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportDaily(Request $request)
    {
        $date = $request->query('date') ? Carbon::parse($request->query('date')) : Carbon::today();

        return Excel::download(new DailyReportExport($date), 'daily_report_' . $date->format('Y-m-d') . '.xlsx');
    }

    /**
     * Export monthly report to Excel.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportMonthly(Request $request)
    {
        $year = $request->query('year') ? (int)$request->query('year') : Carbon::now()->year;
        $month = $request->query('month') ? (int)$request->query('month') : Carbon::now()->month;

        $startDate = Carbon::createFromDate($year, $month, 1);

        return Excel::download(
            new MonthlyReportExport($startDate),
            'monthly_report_' . $startDate->format('Y-m') . '.xlsx'
        );
    }

    /**
     * Export route report to Excel.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportRoutes(Request $request)
    {
        $startDate = $request->query('date_from') ? Carbon::parse($request->query('date_from')) : Carbon::now()->startOfMonth();
        $endDate = $request->query('date_to') ? Carbon::parse($request->query('date_to')) : Carbon::now()->endOfMonth();

        return Excel::download(
            new RouteReportExport($startDate, $endDate),
            'route_report_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export occupancy report to Excel.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportOccupancy(Request $request)
    {
        $startDate = $request->query('date_from') ? Carbon::parse($request->query('date_from')) : Carbon::now()->startOfMonth();
        $endDate = $request->query('date_to') ? Carbon::parse($request->query('date_to')) : Carbon::now()->endOfMonth();
        $ferryId = $request->query('ferry_id');

        return Excel::download(
            new OccupancyReportExport($startDate, $endDate, $ferryId),
            'occupancy_report_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Show detailed information for a specific route.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function routeDetails($id)
    {
        // Get the route information
        $route = Route::findOrFail($id);

        // Get booking statistics for this route
        $bookings = Booking::join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
            ->where('schedules.route_id', $id)
            ->with(['user', 'schedule.ferry'])
            ->orderBy('bookings.created_at', 'desc')
            ->get();

        // Calculate statistics
        $totalPassengers = $bookings->sum('passenger_count');
        $totalRevenue = $bookings->sum('total_amount');
        $averageTicketPrice = $totalPassengers > 0 ? $totalRevenue / $totalPassengers : 0;

        // Get monthly trend data
        $monthlyStats = Booking::join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
            ->where('schedules.route_id', $id)
            ->selectRaw('YEAR(bookings.created_at) as year, MONTH(bookings.created_at) as month, COUNT(*) as booking_count, SUM(bookings.passenger_count) as passenger_count, SUM(bookings.total_amount) as revenue')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return view('admin.reports.route_details', compact(
            'route',
            'bookings',
            'totalPassengers',
            'totalRevenue',
            'averageTicketPrice',
            'monthlyStats'
        ));
    }
}
