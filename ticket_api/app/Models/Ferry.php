<?php
// Ferry.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ferry extends Model
{
    use HasFactory;

    /**
     * Ferry status constants
     */
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_MAINTENANCE = 'MAINTENANCE';
    const STATUS_INACTIVE = 'INACTIVE';

    /**
     * Vehicle type constants
     */
    const VEHICLE_TYPE_MOTORCYCLE = 'motorcycle';
    const VEHICLE_TYPE_CAR = 'car';
    const VEHICLE_TYPE_BUS = 'bus';
    const VEHICLE_TYPE_TRUCK = 'truck';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string, mixed>
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
     *
     * @return HasMany
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Check if the ferry is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if the ferry is in maintenance.
     *
     * @return bool
     */
    public function isInMaintenance(): bool
    {
        return $this->status === self::STATUS_MAINTENANCE;
    }

    /**
     * Check if the ferry is inactive.
     *
     * @return bool
     */
    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    /**
     * Check if the ferry has capacity for a specific type and count.
     *
     * @param string $type
     * @param int $count
     * @return bool
     */
    public function hasCapacityFor(string $type, int $count = 1): bool
    {
        $capacityMap = [
            self::VEHICLE_TYPE_MOTORCYCLE => $this->capacity_vehicle_motorcycle,
            self::VEHICLE_TYPE_CAR => $this->capacity_vehicle_car,
            self::VEHICLE_TYPE_BUS => $this->capacity_vehicle_bus,
            self::VEHICLE_TYPE_TRUCK => $this->capacity_vehicle_truck,
            'passenger' => $this->capacity_passenger,
        ];

        if (!isset($capacityMap[$type])) {
            return false;
        }

        return $capacityMap[$type] >= $count;
    }

    /**
     * Get total vehicle capacity
     *
     * @return int
     */
    public function getTotalVehicleCapacityAttribute(): int
    {
        return $this->capacity_vehicle_motorcycle +
               $this->capacity_vehicle_car +
               $this->capacity_vehicle_bus +
               $this->capacity_vehicle_truck;
    }
}
