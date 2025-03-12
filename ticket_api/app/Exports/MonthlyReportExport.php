<?php

namespace App\Exports;

use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;

class MonthlyReportExport implements WithMultipleSheets
{
    protected $startDate;
    protected $endDate;

    /**
     * @param Carbon $startDate
     */
    public function __construct(Carbon $startDate)
    {
        $this->startDate = $startDate->copy()->startOfMonth();
        $this->endDate = $startDate->copy()->endOfMonth();
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [
            new MonthlyBookingsSheet($this->startDate, $this->endDate),
            new MonthlyRevenueSheet($this->startDate, $this->endDate),
            new MonthlyRoutePerformanceSheet($this->startDate, $this->endDate),
        ];

        return $sheets;
    }
}

class MonthlyBookingsSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithMapping
{
    protected $startDate;
    protected $endDate;

    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     */
    public function __construct(Carbon $startDate, Carbon $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Booking::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->with(['user', 'schedule.route', 'payments' => function ($query) {
                $query->where('status', 'SUCCESS');
            }])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Kode Booking',
            'Pengguna',
            'Rute',
            'Tanggal Keberangkatan',
            'Jumlah Penumpang',
            'Jumlah Kendaraan',
            'Status',
            'Total Pemesanan (Rp)',
            'Status Pembayaran',
            'Tanggal Pemesanan',
        ];
    }

    /**
     * @param Booking $booking
     * @return array
     */
    public function map($booking): array
    {
        $latestPayment = $booking->payments->first();

        return [
            $booking->booking_code,
            $booking->user->name,
            $booking->schedule->route->origin . ' - ' . $booking->schedule->route->destination,
            $booking->booking_date->format('d/m/Y'),
            $booking->passenger_count,
            $booking->vehicle_count,
            $booking->status,
            $booking->total_amount,
            $latestPayment ? $latestPayment->status : 'UNPAID',
            $booking->created_at->format('d/m/Y H:i:s'),
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Bookings';
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15, // Booking Code
            'B' => 25, // User
            'C' => 25, // Route
            'D' => 20, // Departure Date
            'E' => 15, // Passenger Count
            'F' => 15, // Vehicle Count
            'G' => 15, // Status
            'H' => 20, // Amount
            'I' => 15, // Payment Status
            'J' => 20, // Booking Date
        ];
    }
}

class MonthlyRevenueSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithMapping
{
    protected $startDate;
    protected $endDate;

    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     */
    public function __construct(Carbon $startDate, Carbon $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Get daily revenue
        $data = Payment::where('status', 'SUCCESS')
            ->whereBetween('payment_date', [$this->startDate, $this->endDate])
            ->selectRaw('DATE(payment_date) as date, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $data;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Tanggal',
            'Jumlah Pembayaran',
            'Total Pendapatan (Rp)',
        ];
    }

    /**
     * @param object $data
     * @return array
     */
    public function map($data): array
    {
        return [
            Carbon::parse($data->date)->format('d/m/Y'),
            $data->count,
            $data->total,
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Revenue';
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20, // Date
            'B' => 20, // Count
            'C' => 25, // Total
        ];
    }
}

class MonthlyRoutePerformanceSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithMapping
{
    protected $startDate;
    protected $endDate;

    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     */
    public function __construct(Carbon $startDate, Carbon $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Get route performance
        $data = Booking::whereBetween('booking_date', [$this->startDate, $this->endDate])
            ->join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
            ->join('routes', 'schedules.route_id', '=', 'routes.id')
            ->selectRaw('routes.id, routes.origin, routes.destination, COUNT(*) as booking_count, SUM(bookings.passenger_count) as passenger_count, SUM(bookings.total_amount) as revenue')
            ->groupBy('routes.id', 'routes.origin', 'routes.destination')
            ->orderByDesc('passenger_count')
            ->get();

        return $data;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Rute',
            'Jumlah Pemesanan',
            'Jumlah Penumpang',
            'Total Pendapatan (Rp)',
        ];
    }

    /**
     * @param object $data
     * @return array
     */
    public function map($data): array
    {
        return [
            $data->origin . ' - ' . $data->destination,
            $data->booking_count,
            $data->passenger_count,
            $data->revenue,
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Route Performance';
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 30, // Route
            'B' => 20, // Booking Count
            'C' => 20, // Passenger Count
            'D' => 25, // Revenue
        ];
    }
}
