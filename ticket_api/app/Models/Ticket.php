<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
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
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the passenger that owns the ticket.
     */
    public function passenger()
    {
        return $this->belongsTo(Passenger::class);
    }

    /**
     * Get the vehicle associated with the ticket.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Check if the ticket is active.
     */
    public function isActive()
    {
        return $this->status === 'ACTIVE';
    }

    /**
     * Check if the ticket is used.
     */
    public function isUsed()
    {
        return $this->status === 'USED';
    }

    /**
     * Check if the ticket is expired.
     */
    public function isExpired()
    {
        return $this->status === 'EXPIRED';
    }

    /**
     * Check if the ticket is cancelled.
     */
    public function isCancelled()
    {
        return $this->status === 'CANCELLED';
    }

    /**
     * Check if the passenger has boarded.
     */
    public function hasBoarded()
    {
        return $this->boarding_status === 'BOARDED';
    }

    /**
     * Generate a unique ticket code.
     */
    public static function generateTicketCode()
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';

        do {
            $code = '';
            for ($i = 0; $i < 10; $i++) {
                $code .= $characters[rand(0, strlen($characters) - 1)];
            }
        } while (static::where('ticket_code', $code)->exists());

        return $code;
    }
}
