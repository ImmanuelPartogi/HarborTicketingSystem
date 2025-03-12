<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingLog extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'previous_status',
        'new_status',
        'changed_by_type',
        'changed_by_id',
        'notes',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the booking that owns the log.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the user that changed the booking, if any.
     */
    public function user()
    {
        if ($this->changed_by_type === 'USER') {
            return User::find($this->changed_by_id);
        }

        return null;
    }

    /**
     * Get the admin that changed the booking, if any.
     */
    public function admin()
    {
        if ($this->changed_by_type === 'ADMIN') {
            return Admin::find($this->changed_by_id);
        }

        return null;
    }

    /**
     * Get the name of who changed the booking.
     *
     * @return string
     */
    public function getChangedByNameAttribute()
    {
        if ($this->changed_by_type === 'USER') {
            $user = $this->user();
            return $user ? $user->name : 'Unknown User';
        } elseif ($this->changed_by_type === 'ADMIN') {
            $admin = $this->admin();
            return $admin ? $admin->name : 'Unknown Admin';
        } else {
            return 'System';
        }
    }

    /**
     * Create a new log entry for a booking status change.
     *
     * @param Booking $booking
     * @param string $previousStatus
     * @param string $newStatus
     * @param string $changedByType
     * @param int|null $changedById
     * @param string|null $notes
     * @return static
     */
    public static function createLog(
        Booking $booking,
        string $previousStatus,
        string $newStatus,
        string $changedByType = 'SYSTEM',
        ?int $changedById = null,
        ?string $notes = null
    ) {
        return static::create([
            'booking_id' => $booking->id,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'changed_by_type' => $changedByType,
            'changed_by_id' => $changedById,
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }
}
