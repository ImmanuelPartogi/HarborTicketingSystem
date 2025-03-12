<?php

namespace App\Exports;

use App\Models\Booking;
use App\Models\Route;
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

class RouteReportExport implements WithMultipleSheets
{
    protected $startDate;
    protected $endDate;
    protected $routeId;

    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int|null $routeId
     */
    public function __construct(Carbon $startDate, Carbon $endDate, ?int $routeId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->routeId = $routeId;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [
            new RoutePerformanceSheet($this->startDate, $this->endDate),
        ];

        if ($this->routeId) {
            $route = Route::findOrFail($this->routeId);
            $sheets[] = new RouteDetailSheet($this->startDate, $this->endDate, $route);
        }

        return $sheets;
    }
}

class RoutePerformanceSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithMapping
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
        return Booking::whereBetween('booking_date', [$this->startDate, $this->endDate])
            ->join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
            ->join('routes', 'schedules.route_id', '=', 'routes.id')
            ->selectRaw('routes.id, routes.origin, routes.destination, COUNT(*) as booking_count, SUM(bookings.passenger_count) as passenger_count, SUM(bookings.vehicle_count) as vehicle_count, SUM(bookings.total_amount) as revenue')
            ->groupBy('routes.id', 'routes.origin', 'routes.destination')
            ->orderByDesc('passenger_count')
            ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID Rute',
            'Asal',
            'Tujuan',
            'Jumlah Booking',
            'Jumlah Penumpang',
            'Jumlah Kendaraan',
            'Total Pendapatan (Rp)',
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->id,
            $row->origin,
            $row->destination,
            $row->booking_count,
            $row->passenger_count,
            $row->vehicle_count,
            $row->revenue,
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Performance Per Rute';
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 20,
            'C' => 20,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 20,
        ];
    }
}

class RouteDetailSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithMapping
{
    protected $startDate;
    protected $endDate;
    protected $route;

    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param Route $route
     */
    public function __construct(Carbon $startDate, Carbon $endDate, Route $route)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->route = $route;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Booking::whereBetween('booking_date', [$this->startDate, $this->endDate])
            ->whereHas('schedule', function ($query) {
                $query->where('route_id', $this->route->id);
            })
            ->with(['user', 'schedule', 'passengers', 'vehicles'])
            ->orderBy('booking_date')
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
            'Tanggal Perjalanan',
            'Waktu Keberangkatan',
            'Jumlah Penumpang',
            'Jumlah Kendaraan',
            'Tipe Kendaraan',
            'Status',
            'Total (Rp)',
        ];
    }

    /**
     * @param Booking $booking
     * @return array
     */
    public function map($booking): array
    {
        $vehicleTypes = $booking->vehicles->pluck('type')->map(function($type) {
            return match($type) {
                'MOTORCYCLE' => 'Motor',
                'CAR' => 'Mobil',
                'BUS' => 'Bus',
                'TRUCK' => 'Truk',
                default => $type,
            };
        })->implode(', ');

        return [
            $booking->booking_code,
            $booking->user->name,
            $booking->booking_date->format('d/m/Y'),
            $booking->schedule->departure_time->format('H:i'),
            $booking->passenger_count,
            $booking->vehicle_count,
            $vehicleTypes ?: '-',
            $booking->status,
            $booking->total_amount,
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->route->origin . ' - ' . $this->route->destination;
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 25,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 20,
            'H' => 15,
            'I' => 15,
        ];
    }
}
