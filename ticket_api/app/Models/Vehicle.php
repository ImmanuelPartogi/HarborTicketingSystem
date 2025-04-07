<?php
// Vehicle.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vehicle extends Model
{
    use HasFactory;

    /**
     * Vehicle type constants
     */
    const TYPE_MOTORCYCLE = 'MOTORCYCLE';
    const TYPE_CAR = 'CAR';
    const TYPE_BUS = 'BUS';
    const TYPE_TRUCK = 'TRUCK';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string, mixed>
     */
    protected $fillable = [
        'booking_id',
        'type',
        'license_plate',
        'weight',
        'owner_passenger_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'weight' => 'decimal:2',
    ];

    /**
     * Get the booking that owns the vehicle.
     *
     * @return BelongsTo
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the ticket associated with the vehicle.
     *
     * @return HasOne
     */
    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class);
    }

    /**
     * Get the type of the vehicle as a display name.
     *
     * @return string
     */
    public function getTypeNameAttribute(): string
    {
        $types = [
            self::TYPE_MOTORCYCLE => 'Motor',
            self::TYPE_CAR => 'Mobil',
            self::TYPE_BUS => 'Bus',
            self::TYPE_TRUCK => 'Truk',
        ];

        return $types[$this->type] ?? $this->type;
    }

    /**
     * Check if the vehicle is a motorcycle.
     *
     * @return bool
     */
    public function isMotorcycle(): bool
    {
        return $this->type === self::TYPE_MOTORCYCLE;
    }

    /**
     * Check if the vehicle is a car.
     *
     * @return bool
     */
    public function isCar(): bool
    {
        return $this->type === self::TYPE_CAR;
    }

    /**
     * Check if the vehicle is a bus.
     *
     * @return bool
     */
    public function isBus(): bool
    {
        return $this->type === self::TYPE_BUS;
    }

    /**
     * Check if the vehicle is a truck.
     *
     * @return bool
     */
    public function isTruck(): bool
    {
        return $this->type === self::TYPE_TRUCK;
    }

    /**
     * Get the price for this vehicle based on the booking's route
     *
     * @return float|null
     */
    public function getPrice(): ?float
    {
        if (!$this->booking || !$this->booking->schedule || !$this->booking->schedule->route) {
            return null;
        }

        return $this->booking->schedule->route->getPriceForVehicle($this->type);
    }

    /**
     * Get formatted price
     *
     * @return string|null
     */
    public function getFormattedPriceAttribute(): ?string
    {
        $price = $this->getPrice();

        if ($price === null) {
            return null;
        }

        return 'Rp ' . number_format($price, 0, ',', '.');
    }

    /**
     * Get the passenger that owns the vehicle.
     *
     * @return BelongsTo
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Passenger::class, 'owner_passenger_id');
    }
}
