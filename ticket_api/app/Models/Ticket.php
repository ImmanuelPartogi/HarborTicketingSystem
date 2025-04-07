<?php
// Ticket.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    /**
     * Ticket status constants
     */
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_USED = 'USED';
    const STATUS_EXPIRED = 'EXPIRED';
    const STATUS_CANCELLED = 'CANCELLED';

    /**
     * Boarding status constants
     */
    const BOARDING_NOT_BOARDED = 'NOT_BOARDED';
    const BOARDING_BOARDED = 'BOARDED';
    const BOARDING_CANCELLED = 'CANCELLED';
    const BOARDING_EXPIRED = 'EXPIRED';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string, mixed>
     */
    protected $fillable = [
        'ticket_code',
        'booking_id',
        'passenger_id',
        'vehicle_id',
        'qr_code',
        'seat_number',
        'boarding_status',
        'boarding_time',
        'status',
        'checked_in',
        'watermark_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'boarding_time' => 'datetime',
        'checked_in' => 'boolean',
    ];

    /**
     * Get the booking that owns the ticket.
     *
     * @return BelongsTo
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the passenger that owns the ticket.
     * Relasi ini tetap ada tapi tidak wajib.
     *
     * @return BelongsTo
     */
    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class);
    }

    /**
     * Get the vehicle associated with the ticket.
     *
     * @return BelongsTo
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Check if the ticket is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if the ticket is used.
     *
     * @return bool
     */
    public function isUsed(): bool
    {
        return $this->status === self::STATUS_USED;
    }

    /**
     * Check if the ticket is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    /**
     * Check if the ticket is cancelled.
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if the passenger has boarded.
     *
     * @return bool
     */
    public function hasBoarded(): bool
    {
        return $this->boarding_status === self::BOARDING_BOARDED;
    }

    /**
     * Mark the ticket as used
     *
     * @return bool
     */
    public function markAsUsed(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        $this->status = self::STATUS_USED;
        $this->boarding_status = self::BOARDING_BOARDED;
        $this->boarding_time = now();

        return $this->save();
    }

    /**
     * Mark the ticket as checked in
     *
     * @return bool
     */
    public function checkIn(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE || $this->checked_in) {
            return false;
        }

        $this->checked_in = true;

        return $this->save();
    }

    /**
     * Mark the ticket as cancelled
     *
     * @return bool
     */
    public function cancel(): bool
    {
        if ($this->status === self::STATUS_USED || $this->status === self::STATUS_CANCELLED) {
            return false;
        }

        $this->status = self::STATUS_CANCELLED;
        $this->boarding_status = self::BOARDING_CANCELLED;

        return $this->save();
    }

    /**
     * Generate a unique ticket code.
     *
     * @return string
     */
    public static function generateTicketCode(): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';

        do {
            $code = '';
            for ($i = 0; $i < 10; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (static::where('ticket_code', $code)->exists());

        return $code;
    }

    /**
     * Get the ticket type (passenger/vehicle)
     * Dimodifikasi untuk tidak tergantung pada passenger_id
     *
     * @return string
     */
    public function getTicketTypeAttribute(): string
    {
        return $this->vehicle_id ? 'Vehicle' : 'Passenger';
    }

    /**
     * Get the schedule through booking relation.
     */
    public function schedule()
    {
        return $this->hasOneThrough(
            Schedule::class,
            Booking::class,
            'id', // Foreign key on bookings table
            'id', // Foreign key on schedules table
            'booking_id', // Local key on tickets table
            'schedule_id' // Local key on bookings table
        );
    }
}
