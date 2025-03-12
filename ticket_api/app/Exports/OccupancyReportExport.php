<?php

namespace App\Exports;

use App\Models\Schedule;
use App\Models\ScheduleDate;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;

class OccupancyReportExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithMapping
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
        return Schedule::whereHas('scheduleDates', function ($query) {
                $query->where('date', $this->date->format('Y-m-d'));
            })
            ->with(['route', 'ferry', 'scheduleDates' => function ($query) {
                $query->where('date', $this->date->format('Y-m-d'));
            }])
            ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Rute',
            'Kapal',
            'Waktu Keberangkatan',
            'Kapasitas Penumpang',
            'Jumlah Penumpang',
            'Okupansi Penumpang (%)',
            'Motor (%)',
            'Mobil (%)',
            'Bus (%)',
            'Truk (%)',
            'Status',
        ];
    }

    /**
     * @param Schedule $schedule
     * @return array
     */
    public function map($schedule): array
    {
        $scheduleDate = $schedule->scheduleDates->first();
        $passengerOccupancy = 0;
        $motorcycleOccupancy = 0;
        $carOccupancy = 0;
        $busOccupancy = 0;
        $truckOccupancy = 0;

        if ($scheduleDate && $schedule->ferry) {
            // Passenger occupancy
            $passengerOccupancy = $schedule->ferry->capacity_passenger > 0
                ? round(($scheduleDate->passenger_count / $schedule->ferry->capacity_passenger) * 100, 2)
                : 0;

            // Vehicle occupancy
            $motorcycleOccupancy = $schedule->ferry->capacity_vehicle_motorcycle > 0
                ? round(($scheduleDate->motorcycle_count / $schedule->ferry->capacity_vehicle_motorcycle) * 100, 2)
                : 0;

            $carOccupancy = $schedule->ferry->capacity_vehicle_car > 0
                ? round(($scheduleDate->car_count / $schedule->ferry->capacity_vehicle_car) * 100, 2)
                : 0;

            $busOccupancy = $schedule->ferry->capacity_vehicle_bus > 0
                ? round(($scheduleDate->bus_count / $schedule->ferry->capacity_vehicle_bus) * 100, 2)
                : 0;

            $truckOccupancy = $schedule->ferry->capacity_vehicle_truck > 0
                ? round(($scheduleDate->truck_count / $schedule->ferry->capacity_vehicle_truck) * 100, 2)
                : 0;
        }

        return [
            $schedule->id,
            $schedule->route->origin . ' - ' . $schedule->route->destination,
            $schedule->ferry->name,
            $schedule->departure_time->format('H:i'),
            $schedule->ferry->capacity_passenger,
            $scheduleDate ? $scheduleDate->passenger_count : 0,
            $passengerOccupancy,
            $motorcycleOccupancy,
            $carOccupancy,
            $busOccupancy,
            $truckOccupancy,
            $scheduleDate ? $scheduleDate->status : 'N/A',
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Occupancy Report - ' . $this->date->format('d M Y');
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
            'B' => 25,
            'C' => 20,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 15,
            'J' => 15,
            'K' => 15,
            'L' => 15,
        ];
    }
}
