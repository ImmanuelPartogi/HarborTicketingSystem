<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'type',
        'license_plate',
        'weight',
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
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the ticket associated with the vehicle.
     */
    public function ticket()
    {
        return $this->hasOne(Ticket::class);
    }

    /**
     * Get the type of the vehicle as a display name.
     */
    public function getTypeNameAttribute()
    {
        $types = [
            'MOTORCYCLE' => 'Motor',
            'CAR' => 'Mobil',
            'BUS' => 'Bus',
            'TRUCK' => 'Truk',
        ];

        return $types[$this->type] ?? $this->type;
    }

    /**
     * Check if the vehicle is a motorcycle.
     */
    public function isMotorcycle()
    {
        return $this->type === 'MOTORCYCLE';
    }

    /**
     * Check if the vehicle is a car.
     */
    public function isCar()
    {
        return $this->type === 'CAR';
    }

    /**
     * Check if the vehicle is a bus.
     */
    public function isBus()
    {
        return $this->type === 'BUS';
    }

    /**
     * Check if the vehicle is a truck.
     */
    public function isTruck()
    {
        return $this->type === 'TRUCK';
    }
}
