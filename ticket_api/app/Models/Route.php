<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'origin',
        'destination',
        'distance',
        'duration',
        'base_price',
        'motorcycle_price',
        'car_price',
        'bus_price',
        'truck_price',
        'status',
        'status_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'distance' => 'decimal:2',
        'base_price' => 'decimal:2',
        'motorcycle_price' => 'decimal:2',
        'car_price' => 'decimal:2',
        'bus_price' => 'decimal:2',
        'truck_price' => 'decimal:2',
    ];

    /**
     * Get the schedules for the route.
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Get the formatted duration.
     */
    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;

        $formatted = '';
        if ($hours > 0) {
            $formatted .= $hours . ' jam ';
        }
        if ($minutes > 0) {
            $formatted .= $minutes . ' menit';
        }

        return trim($formatted);
    }

    /**
     * Calculate price for a specific vehicle type.
     */
    public function getPriceForVehicle($type)
    {
        switch ($type) {
            case 'MOTORCYCLE':
                return $this->motorcycle_price;
            case 'CAR':
                return $this->car_price;
            case 'BUS':
                return $this->bus_price;
            case 'TRUCK':
                return $this->truck_price;
            default:
                return 0;
        }
    }

    /**
     * Check if the route is active.
     */
    public function isActive()
    {
        return $this->status === 'ACTIVE';
    }
}
