<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
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
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the schedule that owns the booking.
     */
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Get the passengers for the booking.
     */
    public function passengers()
    {
        return $this->hasMany(Passenger::class);
    }

    /**
     * Get the vehicles for the booking.
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * Get the tickets for the booking.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get the payments for the booking.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the refunds for the booking.
     */
    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Get the booking logs for the booking.
     */
    public function logs()
    {
        return $this->hasMany(BookingLog::class);
    }

    /**
     * Check if the booking is confirmed.
     */
    public function isConfirmed()
    {
        return $this->status === 'CONFIRMED';
    }

    /**
     * Check if the booking is completed.
     */
    public function isCompleted()
    {
        return $this->status === 'COMPLETED';
    }

    /**
     * Check if the booking is cancelled.
     */
    public function isCancelled()
    {
        return $this->status === 'CANCELLED';
    }

    /**
     * Check if the booking is refunded.
     */
    public function isRefunded()
    {
        return $this->status === 'REFUNDED';
    }

    /**
     * Check if the booking is rescheduled.
     */
    public function isRescheduled()
    {
        return $this->status === 'RESCHEDULED';
    }

    /**
     * Get the primary passenger for the booking.
     */
    public function primaryPassenger()
    {
        return $this->passengers()->where('is_primary', true)->first();
    }

    /**
     * Get the latest payment for the booking.
     */
    public function latestPayment()
    {
        return $this->payments()->latest()->first();
    }

    /**
     * Generate a unique booking code.
     */
    public static function generateBookingCode()
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';

        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $characters[rand(0, strlen($characters) - 1)];
            }
        } while (static::where('booking_code', $code)->exists());

        return $code;
    }
}
