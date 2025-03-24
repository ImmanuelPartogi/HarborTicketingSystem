<?php
// Schedule.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Schedule extends Model
{
    use HasFactory;

    /**
     * Status constants to ensure consistency
     */
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_CANCELLED = 'CANCELLED';
    const STATUS_DELAYED = 'DELAYED';
    const STATUS_FULL = 'FULL';
    const STATUS_DEPARTED = 'DEPARTED';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string, mixed>
     */
    protected $fillable = [
        'route_id',
        'ferry_id',
        'departure_time',
        'arrival_time',
        'days',
        'status',
        'status_reason',
        'status_updated_at',
        'status_expiry_date',
        'last_adjustment_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // Changed from datetime to string to match the database schema
        'departure_time' => 'string',
        'arrival_time' => 'string',
        'status_updated_at' => 'datetime',
        'status_expiry_date' => 'datetime',
    ];

    /**
     * Get the route that owns the schedule.
     *
     * @return BelongsTo
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the ferry that owns the schedule.
     *
     * @return BelongsTo
     */
    public function ferry(): BelongsTo
    {
        return $this->belongsTo(Ferry::class);
    }

    /**
     * Get the schedule dates for the schedule.
     *
     * @return HasMany
     */
    public function scheduleDates(): HasMany
    {
        return $this->hasMany(ScheduleDate::class);
    }

    /**
     * Get the bookings for the schedule.
     *
     * @return HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Check if the schedule runs on a specific day.
     *
     * @param int $dayNumber
     * @return bool
     */
    public function runsOnDay(int $dayNumber): bool
    {
        return in_array((string)$dayNumber, $this->getDaysArrayAttribute());
    }

    /**
     * Get upcoming schedule dates.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUpcomingDates(int $limit = 7)
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(30);

        return $this->scheduleDates()
            ->whereBetween('date', [$startDate, $endDate])
            ->whereIn('status', [ScheduleDate::STATUS_AVAILABLE, ScheduleDate::STATUS_WEATHER_ISSUE])
            ->orderBy('date')
            ->limit($limit)
            ->get();
    }

    /**
     * Get the days of the week as array.
     *
     * @return array
     */
    public function getDaysArrayAttribute(): array
    {
        return explode(',', $this->days);
    }

    /**
     * Get the days of the week as formatted string.
     *
     * @return string
     */
    public function getFormattedDaysAttribute(): string
    {
        $dayNames = [
            '1' => 'Senin',
            '2' => 'Selasa',
            '3' => 'Rabu',
            '4' => 'Kamis',
            '5' => 'Jumat',
            '6' => 'Sabtu',
            '7' => 'Minggu',
        ];

        $days = array_map(function ($day) use ($dayNames) {
            return $dayNames[$day] ?? '';
        }, $this->getDaysArrayAttribute());

        return implode(', ', $days);
    }

    /**
     * Check if the schedule is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if the schedule is delayed.
     *
     * @return bool
     */
    public function isDelayed(): bool
    {
        return $this->status === self::STATUS_DELAYED;
    }

    /**
     * Get user-friendly status label
     *
     * @return string
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_CANCELLED => 'Dibatalkan',
            self::STATUS_DELAYED => 'Tertunda',
            self::STATUS_FULL => 'Penuh',
            self::STATUS_DEPARTED => 'Berangkat',
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
            self::STATUS_ACTIVE => 'success',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_DELAYED => 'warning',
            self::STATUS_FULL => 'info',
            self::STATUS_DEPARTED => 'primary',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Get the next available scheduled dates
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNextAvailableDates(int $limit = 5)
    {
        return $this->scheduleDates()
            ->where('date', '>=', Carbon::today())
            ->where('status', ScheduleDate::STATUS_AVAILABLE)
            ->orderBy('date')
            ->limit($limit)
            ->get();
    }

    /**
     * Get the weather affected dates
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getWeatherAffectedDates()
    {
        return $this->scheduleDates()
            ->where('date', '>=', Carbon::today())
            ->where('status', ScheduleDate::STATUS_WEATHER_ISSUE)
            ->orderBy('date')
            ->get();
    }

    /**
     * Create default schedule dates for the next 30 days
     * based on the schedule's operating days
     *
     * @return int Number of created dates
     */
    public function createDefaultDatesForUpcomingMonth(): int
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(30);
        $currentDate = $startDate->copy();
        $daysArray = $this->getDaysArrayAttribute();
        $created = 0;

        while ($currentDate->lte($endDate)) {
            $dayOfWeek = $currentDate->dayOfWeek;
            // Convert Sunday from 0 to 7 to match the system
            if ($dayOfWeek === 0) {
                $dayOfWeek = 7;
            }

            if (in_array((string)$dayOfWeek, $daysArray)) {
                ScheduleDate::updateOrCreate(
                    ['schedule_id' => $this->id, 'date' => $currentDate->format('Y-m-d')],
                    [
                        'status' => ScheduleDate::STATUS_AVAILABLE,
                        'passenger_count' => 0,
                        'motorcycle_count' => 0,
                        'car_count' => 0,
                        'bus_count' => 0,
                        'truck_count' => 0
                    ]
                );
                $created++;
            }

            $currentDate->addDay();
        }

        return $created;
    }

    /**
     * Update schedule status and propagate to dates
     *
     * @param string $status
     * @param string|null $reason
     * @param \DateTime|null $expiryDate
     * @return bool
     */
    public function updateStatus(string $status, ?string $reason = null, ?\DateTime $expiryDate = null): bool
    {
        // Don't update if the status is the same
        if ($this->status === $status) {
            return false;
        }

        $oldStatus = $this->status;
        $this->status = $status;
        $this->status_reason = $reason;
        $this->status_updated_at = now();
        $this->status_expiry_date = $expiryDate;

        $saved = $this->save();

        if ($saved) {
            // Update associated schedule dates
            $this->updateDatesBasedOnStatus();

            // Notify affected users
            $this->notifyAffectedUsers($oldStatus, $status, $reason);
        }

        return $saved;
    }

    /**
     * Check if the schedule status is considered final.
     * Final statuses should not be changed automatically.
     *
     * @param string $status
     * @return bool
     */
    public static function isStatusFinal(string $status): bool
    {
        return in_array($status, [self::STATUS_FULL, self::STATUS_DEPARTED]);
    }

    /**
     * Check if current schedule status is considered final.
     *
     * @return bool
     */
    public function hasStatusFinal(): bool
    {
        return self::isStatusFinal($this->status);
    }

    /**
     * Process schedule dates to reflect the schedule's current status,
     * respecting the final statuses (FULL and DEPARTED).
     *
     * @return int Number of affected dates
     */
    public function updateDatesBasedOnStatus(): int
    {
        // Only process for non-active statuses
        if ($this->status === self::STATUS_ACTIVE) {
            return 0;
        }

        $mappedStatus = $this->status === self::STATUS_CANCELLED
            ? ScheduleDate::STATUS_UNAVAILABLE
            : ScheduleDate::STATUS_WEATHER_ISSUE;

        // Get future dates that are not in final status
        $affectedCount = $this->scheduleDates()
            ->where('date', '>=', now()->format('Y-m-d'))
            ->whereNotIn('status', [ScheduleDate::STATUS_FULL, ScheduleDate::STATUS_DEPARTED])
            ->update([
                'status' => $mappedStatus,
                'status_reason' => $this->status_reason ?? ($this->status === self::STATUS_CANCELLED
                    ? 'Jadwal tidak aktif'
                    : 'Jadwal tertunda karena masalah cuaca'),
                'status_expiry_date' => $this->status_expiry_date,
                'modified_by_schedule' => true
            ]);

        return $affectedCount;
    }

    /**
     * Notify users affected by schedule status change
     *
     * @param string $oldStatus
     * @param string $newStatus
     * @param string|null $reason
     * @return void
     */
    protected function notifyAffectedUsers(string $oldStatus, string $newStatus, ?string $reason = null): void
    {
        // Only send notifications for certain status changes
        if (
            $oldStatus === self::STATUS_ACTIVE &&
            ($newStatus === self::STATUS_CANCELLED || $newStatus === self::STATUS_DELAYED)
        ) {

            // Find affected bookings for future dates
            $affectedBookings = $this->bookings()
                ->where('booking_date', '>=', now()->format('Y-m-d'))
                ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_PENDING])
                ->with('user')
                ->get();

            foreach ($affectedBookings as $booking) {
                if ($booking->user) {
                    Notification::createScheduleChangeNotification(
                        $booking->user,
                        $newStatus === self::STATUS_CANCELLED ? 'Jadwal Dibatalkan' : 'Jadwal Tertunda',
                        'Jadwal untuk booking ' . $booking->booking_code . ' pada tanggal ' .
                            $booking->booking_date->format('d M Y') . ' telah ' .
                            ($newStatus === self::STATUS_CANCELLED ? 'dibatalkan' : 'tertunda') .
                            ($reason ? '. Alasan: ' . $reason : '.'),
                        [
                            'booking_id' => $booking->id,
                            'schedule_id' => $this->id,
                            'status' => $newStatus,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Get the formatted departure time
     *
     * @return string
     */
    public function getFormattedDepartureTimeAttribute(): string
    {
        // Check if it's already a string and convert properly
        if (is_string($this->departure_time)) {
            return $this->departure_time;
        }

        return $this->departure_time->format('H:i');
    }

    /**
     * Get the formatted arrival time
     *
     * @return string
     */
    public function getFormattedArrivalTimeAttribute(): string
    {
        // Check if it's already a string and convert properly
        if (is_string($this->arrival_time)) {
            return $this->arrival_time;
        }

        return $this->arrival_time->format('H:i');
    }


    /**
     * Get trip duration in minutes
     *
     * @return int
     */
    public function getTripDurationAttribute(): int
    {
        // Convert string times to Carbon instances for calculation
        $departureTime = Carbon::createFromFormat('H:i', $this->departure_time);
        $arrivalTime = Carbon::createFromFormat('H:i', $this->arrival_time);

        return $departureTime->diffInMinutes($arrivalTime);
    }

    /**
     * Get formatted trip duration
     *
     * @return string
     */
    public function getFormattedTripDurationAttribute(): string
    {
        $minutes = $this->getTripDurationAttribute();
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        $formatted = '';
        if ($hours > 0) {
            $formatted .= $hours . ' jam ';
        }
        if ($remainingMinutes > 0) {
            $formatted .= $remainingMinutes . ' menit';
        }

        return trim($formatted);
    }
}
