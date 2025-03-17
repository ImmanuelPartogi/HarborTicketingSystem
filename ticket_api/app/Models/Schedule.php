<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Schedule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'route_id',
        'ferry_id',
        'departure_time',
        'arrival_time',
        'days',
        'status',
        'status_reason',
        'last_adjustment_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
    ];

    /**
     * Get the route that owns the schedule.
     */
    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the ferry that owns the schedule.
     */
    public function ferry()
    {
        return $this->belongsTo(Ferry::class);
    }

    /**
     * Get the schedule dates for the schedule.
     */
    public function scheduleDates()
    {
        return $this->hasMany(ScheduleDate::class);
    }

    /**
     * Get the bookings for the schedule.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Check if the schedule runs on a specific day.
     */
    public function runsOnDay($dayNumber)
    {
        return in_array($dayNumber, explode(',', $this->days));
    }

    /**
     * Get upcoming schedule dates.
     */
    public function getUpcomingDates($limit = 7)
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(30);

        return $this->scheduleDates()
            ->whereBetween('date', [$startDate, $endDate])
            ->whereIn('status', ['AVAILABLE', 'WEATHER_ISSUE'])
            ->orderBy('date')
            ->limit($limit)
            ->get();
    }

    /**
     * Get the days of the week as array.
     */
    public function getDaysArrayAttribute()
    {
        return explode(',', $this->days);
    }

    /**
     * Get the days of the week as formatted string.
     */
    public function getFormattedDaysAttribute()
    {
        $dayNames = [
            '1' => 'Senin',
            '2' => 'Selasa',
            '3' => 'Rabu',
            '4' => 'Kamis',
            '5' => 'Jumat',
            '6' => 'Sabtu',
            '7' => 'Minggu',
        ];

        $days = array_map(function ($day) use ($dayNames) {
            return $dayNames[$day] ?? '';
        }, explode(',', $this->days));

        return implode(', ', $days);
    }

    /**
     * Check if the schedule is active.
     */
    public function isActive()
    {
        return $this->status === 'ACTIVE';
    }

    /**
     * Check if the schedule is delayed.
     */
    public function isDelayed()
    {
        return $this->status === 'DELAYED';
    }

    /**
     * Get user-friendly status label
     *
     * @return string
     */
    public function getStatusLabelAttribute()
    {
        switch ($this->status) {
            case 'ACTIVE':
                return 'Aktif';
            case 'CANCELLED':
                return 'Dibatalkan';
            case 'DELAYED':
                return 'Tertunda';
            case 'FULL':
                return 'Penuh';
            default:
                return $this->status;
        }
    }

    /**
     * Get status color for UI
     *
     * @return string
     */
    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case 'ACTIVE':
                return 'success';
            case 'CANCELLED':
                return 'danger';
            case 'DELAYED':
                return 'warning';
            case 'FULL':
                return 'info';
            default:
                return 'secondary';
        }
    }

    /**
     * Get the next available scheduled dates
     */
    public function getNextAvailableDates($limit = 5)
    {
        return $this->scheduleDates()
            ->where('date', '>=', Carbon::today())
            ->where('status', 'AVAILABLE')
            ->orderBy('date')
            ->limit($limit)
            ->get();
    }

    /**
     * Get the weather affected dates
     */
    public function getWeatherAffectedDates()
    {
        return $this->scheduleDates()
            ->where('date', '>=', Carbon::today())
            ->where('status', 'WEATHER_ISSUE')
            ->orderBy('date')
            ->get();
    }

    /**
     * Create default schedule dates for the next 30 days
     * based on the schedule's operating days
     */
    public function createDefaultDatesForUpcomingMonth()
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(30);
        $currentDate = $startDate->copy();
        $daysArray = $this->getDaysArrayAttribute();
        $created = 0;

        while ($currentDate->lte($endDate)) {
            $dayOfWeek = $currentDate->dayOfWeek;
            // Convert Sunday from 0 to 7 to match the system
            if ($dayOfWeek === 0) {
                $dayOfWeek = 7;
            }

            if (in_array((string)$dayOfWeek, $daysArray)) {
                ScheduleDate::updateOrCreate(
                    ['schedule_id' => $this->id, 'date' => $currentDate->format('Y-m-d')],
                    [
                        'status' => 'AVAILABLE',
                        'passenger_count' => 0,
                        'motorcycle_count' => 0,
                        'car_count' => 0,
                        'bus_count' => 0,
                        'truck_count' => 0
                    ]
                );
                $created++;
            }
            $currentDate->addDay();
        }

        return $created;
    }
}
