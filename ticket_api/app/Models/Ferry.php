<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ferry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'capacity_passenger',
        'capacity_vehicle_motorcycle',
        'capacity_vehicle_car',
        'capacity_vehicle_bus',
        'capacity_vehicle_truck',
        'status',
        'description',
        'image',
    ];

    /**
     * Get the schedules for the ferry.
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Check if the ferry is active.
     */
    public function isActive()
    {
        return $this->status === 'ACTIVE';
    }

    /**
     * Check if the ferry has capacity for a specific type and count.
     */
    public function hasCapacityFor($type, $count)
    {
        switch ($type) {
            case 'passenger':
                return $this->capacity_passenger >= $count;
            case 'motorcycle':
                return $this->capacity_vehicle_motorcycle >= $count;
            case 'car':
                return $this->capacity_vehicle_car >= $count;
            case 'bus':
                return $this->capacity_vehicle_bus >= $count;
            case 'truck':
                return $this->capacity_vehicle_truck >= $count;
            default:
                return false;
        }
    }
}
