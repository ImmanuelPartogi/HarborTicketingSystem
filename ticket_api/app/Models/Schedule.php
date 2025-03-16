<?php

namespace App\Models;

use App\Traits\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory, HasStatusHistory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'route_id',
        'ferry_id',
        'departure_time',
        'arrival_time',
        'days',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
    ];

    /**
     * Get the route that owns the schedule.
     */
    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the ferry that owns the schedule.
     */
    public function ferry()
    {
        return $this->belongsTo(Ferry::class);
    }

    /**
     * Get the schedule dates for the schedule.
     */
    public function scheduleDates()
    {
        return $this->hasMany(ScheduleDate::class);
    }

    /**
     * Get the bookings for the schedule.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Check if the schedule runs on a specific day.
     */
    public function runsOnDay($dayNumber)
    {
        return in_array($dayNumber, explode(',', $this->days));
    }

    /**
     * Get the days of the week as array.
     */
    public function getDaysArrayAttribute()
    {
        return explode(',', $this->days);
    }

    /**
     * Get the days of the week as formatted string.
     */
    public function getFormattedDaysAttribute()
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
        }, explode(',', $this->days));

        return implode(', ', $days);
    }

    /**
     * Check if the schedule is active.
     */
    public function isActive()
    {
        return $this->status === 'ACTIVE';
    }

    /**
     * Update schedule status and record history
     */
    public function updateStatus($newStatus, $reason = null, $notes = null)
    {
        $oldStatus = $this->status;

        if ($oldStatus === $newStatus) {
            return false;
        }

        $this->status = $newStatus;
        $saved = $this->save();

        if ($saved) {
            $this->recordStatusChange($oldStatus, $newStatus, $reason, $notes);
        }

        return $saved;
    }
}
