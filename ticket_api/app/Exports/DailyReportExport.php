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

class DailyReportExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithMapping
{
    protected $date;

    /**
     * @param Carbon $date
     */
    public function __construct(Carbon $date)
    {
        $this->date = $date;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Booking::whereDate('created_at', $this->date)
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
            'Jam Keberangkatan',
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
            $booking->schedule->departure_time->format('H:i'),
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
        return 'Laporan Harian - ' . $this->date->format('d M Y');
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
            'E' => 15, // Departure Time
            'F' => 15, // Passenger Count
            'G' => 15, // Vehicle Count
            'H' => 15, // Status
            'I' => 20, // Amount
            'J' => 15, // Payment Status
            'K' => 20, // Booking Date
        ];
    }
}
