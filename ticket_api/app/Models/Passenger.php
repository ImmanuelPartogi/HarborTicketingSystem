<?php
// Passenger.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Passenger extends Model
{
    use HasFactory;

    /**
     * ID type constants
     */
    const ID_TYPE_KTP = 'KTP';
    const ID_TYPE_SIM = 'SIM';
    const ID_TYPE_PASPOR = 'PASPOR';

    /**
     * Gender constants
     */
    const GENDER_MALE = 'MALE';
    const GENDER_FEMALE = 'FEMALE';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string, mixed>
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
     *
     * @return BelongsTo
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the ticket for the passenger.
     *
     * @return HasOne
     */
    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class);
    }

    /**
     * Check if the passenger is an adult.
     *
     * @return bool
     */
    public function isAdult(): bool
    {
        return $this->dob->diffInYears(now()) >= 18;
    }

    /**
     * Check if the passenger is a child.
     *
     * @return bool
     */
    public function isChild(): bool
    {
        $age = $this->dob->diffInYears(now());
        return $age < 18 && $age >= 2;
    }

    /**
     * Check if the passenger is an infant.
     *
     * @return bool
     */
    public function isInfant(): bool
    {
        return $this->dob->diffInYears(now()) < 2;
    }

    /**
     * Get the age of the passenger.
     *
     * @return int
     */
    public function getAgeAttribute(): int
    {
        return $this->dob->diffInYears(now());
    }

    /**
     * Get formatted ID information
     *
     * @return string
     */
    public function getFormattedIdAttribute(): string
    {
        return $this->id_type . ': ' . $this->id_number;
    }
}
