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
     * @var array<int, string>
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
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
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
}
