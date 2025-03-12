<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Passenger extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'name',
        'id_number',
        'id_type',
        'dob',
        'gender',
        'is_primary',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'dob' => 'date',
        'is_primary' => 'boolean',
    ];

    /**
     * Get the booking that owns the passenger.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the ticket for the passenger.
     */
    public function ticket()
    {
        return $this->hasOne(Ticket::class);
    }

    /**
     * Check if the passenger is an adult.
     */
    public function isAdult()
    {
        return $this->dob->diffInYears(now()) >= 18;
    }

    /**
     * Get the age of the passenger.
     */
    public function getAgeAttribute()
    {
        return $this->dob->diffInYears(now());
    }
}
