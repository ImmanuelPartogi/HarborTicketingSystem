<?php
// Booking.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    /**
     * Booking status constants
     */
    const STATUS_PENDING = 'PENDING';
    const STATUS_CONFIRMED = 'CONFIRMED';
    const STATUS_CANCELLED = 'CANCELLED';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_REFUNDED = 'REFUNDED';
    const STATUS_RESCHEDULED = 'RESCHEDULED';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string, mixed>
     */
    protected $fillable = [
        'booking_code',
        'user_id',
        'schedule_id',
        'booking_date',
        'passenger_count',
        'vehicle_count',
        'total_amount',
        'status',
        'cancellation_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'booking_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the booking.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the schedule that owns the booking.
     *
     * @return BelongsTo
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Get the schedule date for this booking
     *
     * @return ScheduleDate|null
     */
    public function scheduleDate(): ?ScheduleDate
    {
        return $this->schedule->scheduleDates()
            ->where('date', $this->booking_date)
            ->first();
    }

    /**
     * Get the passengers for the booking.
     *
     * @return HasMany
     */
    public function passengers(): HasMany
    {
        return $this->hasMany(Passenger::class);
    }

    /**
     * Get the vehicles for the booking.
     *
     * @return HasMany
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * Get the tickets for the booking.
     *
     * @return HasMany
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get the payments for the booking.
     *
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the refunds for the booking.
     *
     * @return HasMany
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Get the booking logs for the booking.
     *
     * @return HasMany
     */
    public function logs(): HasMany
    {
        return $this->hasMany(BookingLog::class);
    }

    /**
     * Check if the booking is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the booking is confirmed.
     *
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if the booking is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the booking is cancelled.
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if the booking is refunded.
     *
     * @return bool
     */
    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    /**
     * Check if the booking is rescheduled.
     *
     * @return bool
     */
    public function isRescheduled(): bool
    {
        return $this->status === self::STATUS_RESCHEDULED;
    }

    /**
     * Get the primary passenger for the booking.
     *
     * @return Passenger|null
     */
    public function primaryPassenger(): ?Passenger
    {
        return $this->passengers()->where('is_primary', true)->first();
    }

    /**
     * Get the latest payment for the booking.
     *
     * @return Payment|null
     */
    public function latestPayment(): ?Payment
    {
        return $this->payments()->latest()->first();
    }

    /**
     * Get successful payment for the booking.
     *
     * @return Payment|null
     */
    public function successfulPayment(): ?Payment
    {
        return $this->payments()->where('status', Payment::STATUS_SUCCESS)->latest()->first();
    }

    /**
     * Change booking status and log the change
     *
     * @param string $newStatus
     * @param string $changedByType
     * @param int|null $changedById
     * @param string|null $notes
     * @return bool
     */
    public function changeStatus(
        string $newStatus,
        string $changedByType = 'SYSTEM',
        ?int $changedById = null,
        ?string $notes = null
    ): bool {
        $previousStatus = $this->status;

        if ($previousStatus === $newStatus) {
            return false;
        }

        $this->status = $newStatus;
        $success = $this->save();

        if ($success) {
            BookingLog::createLog(
                $this,
                $previousStatus,
                $newStatus,
                $changedByType,
                $changedById,
                $notes
            );

            // Trigger appropriate actions based on new status
            if ($newStatus === self::STATUS_CANCELLED) {
                $this->cancelBookingResources();
            } elseif ($newStatus === self::STATUS_CONFIRMED && $previousStatus === self::STATUS_PENDING) {
                $this->generateTickets();
            }
        }

        return $success;
    }

    /**
     * Generate tickets for confirmed booking
     *
     * @return void
     */
    protected function generateTickets(): void
    {
        // Generate passenger tickets
        foreach ($this->passengers as $passenger) {
            if (!$passenger->ticket) {
                Ticket::create([
                    'ticket_code' => Ticket::generateTicketCode(),
                    'booking_id' => $this->id,
                    'passenger_id' => $passenger->id,
                    'qr_code' => md5($this->booking_code . $passenger->id . time()),
                    'status' => Ticket::STATUS_ACTIVE,
                ]);
            }
        }

        // Generate vehicle tickets
        foreach ($this->vehicles as $vehicle) {
            if (!$vehicle->ticket) {
                Ticket::create([
                    'ticket_code' => Ticket::generateTicketCode(),
                    'booking_id' => $this->id,
                    'vehicle_id' => $vehicle->id,
                    'qr_code' => md5($this->booking_code . 'v' . $vehicle->id . time()),
                    'status' => Ticket::STATUS_ACTIVE,
                ]);
            }
        }
    }

    /**
     * Release resources when a booking is cancelled
     *
     * @return void
     */
    protected function cancelBookingResources(): void
    {
        // Update schedule date capacity
        $scheduleDate = $this->scheduleDate();
        if ($scheduleDate) {
            $scheduleDate->passenger_count -= $this->passenger_count;

            // Update vehicle counts
            foreach ($this->vehicles as $vehicle) {
                switch ($vehicle->type) {
                    case Vehicle::TYPE_MOTORCYCLE:
                        $scheduleDate->motorcycle_count--;
                        break;
                    case Vehicle::TYPE_CAR:
                        $scheduleDate->car_count--;
                        break;
                    case Vehicle::TYPE_BUS:
                        $scheduleDate->bus_count--;
                        break;
                    case Vehicle::TYPE_TRUCK:
                        $scheduleDate->truck_count--;
                        break;
                }
            }

            $scheduleDate->save();
        }

        // Cancel tickets
        foreach ($this->tickets as $ticket) {
            $ticket->status = Ticket::STATUS_CANCELLED;
            $ticket->save();
        }
    }

    /**
     * Generate a unique booking code.
     *
     * @return string
     */
    public static function generateBookingCode(): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';

        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (static::where('booking_code', $code)->exists());

        return $code;
    }

    /**
     * Get formatted total amount
     *
     * @return string
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }
}
