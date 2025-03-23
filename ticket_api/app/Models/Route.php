<?php
// Route.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    use HasFactory;

    /**
     * Route status constants
     */
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_INACTIVE = 'INACTIVE';
    const STATUS_WEATHER_ISSUE = 'WEATHER_ISSUE';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string, mixed>
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
        'status_updated_at',
        'status_expiry_date',
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
        'status_updated_at' => 'datetime',
        'status_expiry_date' => 'datetime',
    ];

    /**
     * Get the schedules for the route.
     *
     * @return HasMany
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Get the formatted duration.
     *
     * @return string
     */
    public function getFormattedDurationAttribute(): string
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
     *
     * @param string $type
     * @return float
     */
    public function getPriceForVehicle(string $type): float
    {
        $priceMap = [
            Vehicle::TYPE_MOTORCYCLE => $this->motorcycle_price,
            Vehicle::TYPE_CAR => $this->car_price,
            Vehicle::TYPE_BUS => $this->bus_price,
            Vehicle::TYPE_TRUCK => $this->truck_price,
        ];

        return $priceMap[$type] ?? 0;
    }

    /**
     * Check if the route is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if the route has weather issues.
     *
     * @return bool
     */
    public function hasWeatherIssue(): bool
    {
        return $this->status === self::STATUS_WEATHER_ISSUE;
    }

    /**
     * Update route status and cascade changes to schedules and schedule dates
     *
     * @param string $status
     * @param string|null $reason
     * @param \DateTime|null $expiryDate
     * @return bool
     */
    public function updateStatus(string $status, ?string $reason = null, ?\DateTime $expiryDate = null): bool
    {
        if ($this->status === $status) {
            return false;
        }

        $oldStatus = $this->status;
        $this->status = $status;
        $this->status_reason = $reason;
        $this->status_updated_at = now();
        $this->status_expiry_date = $expiryDate;

        $success = $this->save();

        if ($success) {
            // Propagate status change to associated schedules
            foreach ($this->schedules as $schedule) {
                // If it's a weather issue or inactive, update the schedule
                if ($status === self::STATUS_WEATHER_ISSUE) {
                    $schedule->updateStatus(
                        Schedule::STATUS_DELAYED,
                        $reason ?? 'Route affected by weather issues',
                        $expiryDate
                    );
                } elseif ($status === self::STATUS_INACTIVE) {
                    $schedule->updateStatus(
                        Schedule::STATUS_CANCELLED,
                        $reason ?? 'Route is inactive'
                    );
                } elseif ($status === self::STATUS_ACTIVE &&
                          ($oldStatus === self::STATUS_WEATHER_ISSUE || $oldStatus === self::STATUS_INACTIVE)) {
                    // Reactivate the schedule if the route becomes active again
                    $schedule->updateStatus(
                        Schedule::STATUS_ACTIVE,
                        $reason ?? 'Route is active again'
                    );
                }
            }
        }

        return $success;
    }

    /**
     * Get the formatted base price
     *
     * @return string
     */
    public function getFormattedBasePriceAttribute(): string
    {
        return 'Rp ' . number_format($this->base_price, 0, ',', '.');
    }

    /**
     * Get all unique origins from routes
     *
     * @return array
     */
    public static function getAllOrigins(): array
    {
        return self::select('origin')->distinct()->orderBy('origin')->pluck('origin')->toArray();
    }

    /**
     * Get all unique destinations from routes
     *
     * @return array
     */
    public static function getAllDestinations(): array
    {
        return self::select('destination')->distinct()->orderBy('destination')->pluck('destination')->toArray();
    }
}
