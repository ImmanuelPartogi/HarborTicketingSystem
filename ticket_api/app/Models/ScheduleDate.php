<?php
// ScheduleDate.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduleDate extends Model
{
    use HasFactory;

    /**
     * Status constants for consistency
     */
    const STATUS_AVAILABLE = 'AVAILABLE';
    const STATUS_UNAVAILABLE = 'UNAVAILABLE';
    const STATUS_FULL = 'FULL';
    const STATUS_CANCELLED = 'CANCELLED';
    const STATUS_DEPARTED = 'DEPARTED';
    const STATUS_WEATHER_ISSUE = 'WEATHER_ISSUE';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string, mixed>
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
        'status_expiry_date',
        'modified_by_schedule',
        'adjustment_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'status_expiry_date' => 'datetime',
        'modified_by_schedule' => 'boolean',
    ];

    /**
     * Get the schedule that owns the schedule date.
     *
     * @return BelongsTo
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Get the bookings for the schedule date.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function bookings()
    {
        return Booking::where('schedule_id', $this->schedule_id)
            ->where('booking_date', $this->date)
            ->get();
    }

    /**
     * Check if the schedule date has capacity for more passengers.
     *
     * @param int $count
     * @return bool
     */
    public function hasPassengerCapacity(int $count = 1): bool
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
    public function hasVehicleCapacity(string $type, int $count = 1): bool
    {
        $ferry = $this->schedule->ferry;
        $capacityMap = [
            Vehicle::TYPE_MOTORCYCLE => [
                'current' => $this->motorcycle_count,
                'capacity' => $ferry->capacity_vehicle_motorcycle
            ],
            Vehicle::TYPE_CAR => [
                'current' => $this->car_count,
                'capacity' => $ferry->capacity_vehicle_car
            ],
            Vehicle::TYPE_BUS => [
                'current' => $this->bus_count,
                'capacity' => $ferry->capacity_vehicle_bus
            ],
            Vehicle::TYPE_TRUCK => [
                'current' => $this->truck_count,
                'capacity' => $ferry->capacity_vehicle_truck
            ],
        ];

        if (!isset($capacityMap[$type])) {
            return false;
        }

        return ($capacityMap[$type]['current'] + $count) <= $capacityMap[$type]['capacity'];
    }

    /**
     * Add passengers and vehicles to count
     *
     * @param int $passengerCount
     * @param array $vehicleCounts
     * @return bool
     */
    public function addBookingCounts(int $passengerCount, array $vehicleCounts = []): bool
    {
        // First check if we have capacity
        if (!$this->hasPassengerCapacity($passengerCount)) {
            return false;
        }

        foreach ($vehicleCounts as $type => $count) {
            if (!$this->hasVehicleCapacity($type, $count)) {
                return false;
            }
        }

        // Add to the counts
        $this->passenger_count += $passengerCount;

        foreach ($vehicleCounts as $type => $count) {
            switch ($type) {
                case Vehicle::TYPE_MOTORCYCLE:
                    $this->motorcycle_count += $count;
                    break;
                case Vehicle::TYPE_CAR:
                    $this->car_count += $count;
                    break;
                case Vehicle::TYPE_BUS:
                    $this->bus_count += $count;
                    break;
                case Vehicle::TYPE_TRUCK:
                    $this->truck_count += $count;
                    break;
            }
        }

        // Check if full now
        $this->checkIfFull();

        return $this->save();
    }

    /**
     * Update status to full if capacity reached
     */
    protected function checkIfFull(): void
    {
        $ferry = $this->schedule->ferry;

        // Check passenger capacity
        $passengerFull = ($this->passenger_count >= $ferry->capacity_passenger);

        // Check vehicle capacities
        $motorcycleFull = ($this->motorcycle_count >= $ferry->capacity_vehicle_motorcycle);
        $carFull = ($this->car_count >= $ferry->capacity_vehicle_car);
        $busFull = ($this->bus_count >= $ferry->capacity_vehicle_bus);
        $truckFull = ($this->truck_count >= $ferry->capacity_vehicle_truck);

        if ($passengerFull || ($motorcycleFull && $carFull && $busFull && $truckFull)) {
            $this->status = self::STATUS_FULL;
        }
    }

    /**
     * Check if the schedule date is available.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    /**
     * Check if the schedule date is affected by weather issues.
     *
     * @return bool
     */
    public function isWeatherAffected(): bool
    {
        return $this->status === self::STATUS_WEATHER_ISSUE;
    }

    /**
     * Check if the status is in final state (cannot be changed).
     *
     * @return bool
     */
    public function isFinalState(): bool
    {
        return in_array($this->status, [self::STATUS_FULL, self::STATUS_DEPARTED]);
    }

    /**
     * Check if the status can be directly edited.
     * Prevents editing schedule dates that were modified by schedule status changes.
     *
     * @return bool
     */
    public function canEditStatus(): bool
    {
        // Cannot edit if in final state
        if ($this->isFinalState()) {
            return false;
        }

        // Cannot edit if modified by schedule and not yet expired
        if (
            $this->modified_by_schedule &&
            ($this->status === self::STATUS_WEATHER_ISSUE || $this->status === self::STATUS_UNAVAILABLE)
        ) {
            // If it's a weather issue with an expiry date, check if it's expired
            if ($this->status === self::STATUS_WEATHER_ISSUE && $this->status_expiry_date) {
                return now()->gt($this->status_expiry_date);
            }
            return false;
        }

        return true;
    }

    /**
     * Get the remaining passenger capacity.
     *
     * @return int
     */
    public function getRemainingPassengerCapacityAttribute(): int
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
    public function getRemainingVehicleCapacity(string $type): int
    {
        $ferry = $this->schedule->ferry;
        $capacityMap = [
            Vehicle::TYPE_MOTORCYCLE => [
                'current' => $this->motorcycle_count,
                'capacity' => $ferry->capacity_vehicle_motorcycle
            ],
            Vehicle::TYPE_CAR => [
                'current' => $this->car_count,
                'capacity' => $ferry->capacity_vehicle_car
            ],
            Vehicle::TYPE_BUS => [
                'current' => $this->bus_count,
                'capacity' => $ferry->capacity_vehicle_bus
            ],
            Vehicle::TYPE_TRUCK => [
                'current' => $this->truck_count,
                'capacity' => $ferry->capacity_vehicle_truck
            ],
        ];

        if (!isset($capacityMap[$type])) {
            return 0;
        }

        return max(0, $capacityMap[$type]['capacity'] - $capacityMap[$type]['current']);
    }

    /**
     * Update status with notification to affected bookings
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

        // Cannot update if in final state
        if ($this->isFinalState()) {
            return false;
        }

        // Cannot update if modified by schedule and not yet expired
        if (!$this->canEditStatus()) {
            return false;
        }

        $oldStatus = $this->status;
        $this->status = $status;
        $this->status_reason = $reason;
        $this->status_expiry_date = $expiryDate;
        $this->modified_by_schedule = false;

        $saved = $this->save();

        if ($saved) {
            // Notify affected users
            $this->notifyAffectedUsers($oldStatus, $status, $reason);
        }

        return $saved;
    }

    /**
     * Get user-friendly status label
     *
     * @return string
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            self::STATUS_AVAILABLE => 'Tersedia',
            self::STATUS_UNAVAILABLE => 'Tidak Tersedia',
            self::STATUS_FULL => 'Penuh',
            self::STATUS_CANCELLED => 'Dibatalkan',
            self::STATUS_DEPARTED => 'Berangkat',
            self::STATUS_WEATHER_ISSUE => 'Masalah Cuaca',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get status color for UI
     *
     * @return string
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            self::STATUS_AVAILABLE => 'success',
            self::STATUS_UNAVAILABLE => 'secondary',
            self::STATUS_FULL => 'warning',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_DEPARTED => 'info',
            self::STATUS_WEATHER_ISSUE => 'warning',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Notify users affected by schedule date status change
     *
     * @param string $oldStatus
     * @param string $newStatus
     * @param string|null $reason
     * @return void
     */
    protected function notifyAffectedUsers(string $oldStatus, string $newStatus, ?string $reason = null): void
    {
        // Only send notifications for certain status changes
        if ($oldStatus === self::STATUS_AVAILABLE &&
            ($newStatus === self::STATUS_CANCELLED || $newStatus === self::STATUS_WEATHER_ISSUE ||
             $newStatus === self::STATUS_UNAVAILABLE)) {

            // Find affected bookings
            $affectedBookings = Booking::where('schedule_id', $this->schedule_id)
                ->where('booking_date', $this->date)
                ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_PENDING])
                ->with('user')
                ->get();

            foreach ($affectedBookings as $booking) {
                if ($booking->user) {
                    $title = '';
                    $message = '';

                    if ($newStatus === self::STATUS_CANCELLED) {
                        $title = 'Jadwal Dibatalkan';
                        $message = 'Jadwal untuk booking ' . $booking->booking_code . ' pada tanggal ' .
                                  $this->date->format('d M Y') . ' telah dibatalkan';
                    } elseif ($newStatus === self::STATUS_WEATHER_ISSUE) {
                        $title = 'Jadwal Tertunda - Masalah Cuaca';
                        $message = 'Jadwal untuk booking ' . $booking->booking_code . ' pada tanggal ' .
                                  $this->date->format('d M Y') . ' tertunda karena masalah cuaca';
                    } elseif ($newStatus === self::STATUS_UNAVAILABLE) {
                        $title = 'Jadwal Tidak Tersedia';
                        $message = 'Jadwal untuk booking ' . $booking->booking_code . ' pada tanggal ' .
                                  $this->date->format('d M Y') . ' tidak tersedia';
                    }

                    if ($reason) {
                        $message .= '. Alasan: ' . $reason;
                    } else {
                        $message .= '.';
                    }

                    Notification::createScheduleChangeNotification(
                        $booking->user,
                        $title,
                        $message,
                        [
                            'booking_id' => $booking->id,
                            'schedule_id' => $this->schedule_id,
                            'date' => $this->date->format('Y-m-d'),
                            'status' => $newStatus,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Get formatted date
     *
     * @return string
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->date->format('d M Y');
    }

    /**
     * Get day name (in Indonesian)
     *
     * @return string
     */
    public function getDayNameAttribute(): string
    {
        $dayNames = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];

        return $dayNames[$this->date->format('l')] ?? $this->date->format('l');
    }
}
