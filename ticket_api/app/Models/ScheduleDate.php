<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleDate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'schedule_id',
        'date',
        'passenger_count',
        'motorcycle_count',
        'car_count',
        'bus_count',
        'truck_count',
        'status',
        'status_reason',
        'adjustment_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the schedule that owns the schedule date.
     */
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Check if the schedule date has capacity for more passengers.
     *
     * @param int $count
     * @return bool
     */
    public function hasPassengerCapacity(int $count = 1)
    {
        $ferry = $this->schedule->ferry;
        return ($this->passenger_count + $count) <= $ferry->capacity_passenger;
    }

    /**
     * Check if the schedule date has capacity for more vehicles of a specific type.
     *
     * @param string $type
     * @param int $count
     * @return bool
     */
    public function hasVehicleCapacity(string $type, int $count = 1)
    {
        $ferry = $this->schedule->ferry;

        switch ($type) {
            case 'MOTORCYCLE':
                return ($this->motorcycle_count + $count) <= $ferry->capacity_vehicle_motorcycle;
            case 'CAR':
                return ($this->car_count + $count) <= $ferry->capacity_vehicle_car;
            case 'BUS':
                return ($this->bus_count + $count) <= $ferry->capacity_vehicle_bus;
            case 'TRUCK':
                return ($this->truck_count + $count) <= $ferry->capacity_vehicle_truck;
            default:
                return false;
        }
    }

    /**
     * Check if the schedule date is available.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->status === 'AVAILABLE';
    }

    /**
     * Check if the schedule date is affected by weather issues.
     *
     * @return bool
     */
    public function isWeatherAffected()
    {
        return $this->status === 'WEATHER_ISSUE';
    }

    /**
     * Get the remaining passenger capacity.
     *
     * @return int
     */
    public function getRemainingPassengerCapacityAttribute()
    {
        $ferry = $this->schedule->ferry;
        return max(0, $ferry->capacity_passenger - $this->passenger_count);
    }

    /**
     * Get the remaining vehicle capacity for a specific type.
     *
     * @param string $type
     * @return int
     */
    public function getRemainingVehicleCapacity(string $type)
    {
        $ferry = $this->schedule->ferry;

        switch ($type) {
            case 'MOTORCYCLE':
                return max(0, $ferry->capacity_vehicle_motorcycle - $this->motorcycle_count);
            case 'CAR':
                return max(0, $ferry->capacity_vehicle_car - $this->car_count);
            case 'BUS':
                return max(0, $ferry->capacity_vehicle_bus - $this->bus_count);
            case 'TRUCK':
                return max(0, $ferry->capacity_vehicle_truck - $this->truck_count);
            default:
                return 0;
        }
    }

    /**
     * Get user-friendly status label
     *
     * @return string
     */
    public function getStatusLabelAttribute()
    {
        switch ($this->status) {
            case 'AVAILABLE':
                return 'Tersedia';
            case 'UNAVAILABLE':
                return 'Tidak Tersedia';
            case 'FULL':
                return 'Penuh';
            case 'CANCELLED':
                return 'Dibatalkan';
            case 'DEPARTED':
                return 'Berangkat';
            case 'WEATHER_ISSUE':
                return 'Masalah Cuaca';
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
            case 'AVAILABLE':
                return 'success';
            case 'UNAVAILABLE':
                return 'secondary';
            case 'FULL':
                return 'warning';
            case 'CANCELLED':
                return 'danger';
            case 'DEPARTED':
                return 'info';
            case 'WEATHER_ISSUE':
                return 'warning';
            default:
                return 'secondary';
        }
    }
}
