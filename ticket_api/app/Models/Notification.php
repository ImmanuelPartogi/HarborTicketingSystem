<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'is_read',
        'data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the data as an array.
     *
     * @return array|null
     */
    public function getDataArrayAttribute()
    {
        if (!$this->data) {
            return null;
        }

        return json_decode($this->data, true);
    }

    /**
     * Mark the notification as read.
     *
     * @return $this
     */
    public function markAsRead()
    {
        $this->is_read = true;
        $this->save();

        return $this;
    }

    /**
     * Create a new booking notification.
     *
     * @param User $user
     * @param string $title
     * @param string $message
     * @param array|null $data
     * @return static
     */
    public static function createBookingNotification(User $user, string $title, string $message, ?array $data = null)
    {
        return static::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => 'BOOKING',
            'is_read' => false,
            'data' => $data ? json_encode($data) : null,
        ]);
    }

    /**
     * Create a new payment notification.
     *
     * @param User $user
     * @param string $title
     * @param string $message
     * @param array|null $data
     * @return static
     */
    public static function createPaymentNotification(User $user, string $title, string $message, ?array $data = null)
    {
        return static::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => 'PAYMENT',
            'is_read' => false,
            'data' => $data ? json_encode($data) : null,
        ]);
    }

    /**
     * Create a new schedule change notification.
     *
     * @param User $user
     * @param string $title
     * @param string $message
     * @param array|null $data
     * @return static
     */
    public static function createScheduleChangeNotification(User $user, string $title, string $message, ?array $data = null)
    {
        return static::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => 'SCHEDULE_CHANGE',
            'is_read' => false,
            'data' => $data ? json_encode($data) : null,
        ]);
    }

    /**
     * Create a new boarding notification.
     *
     * @param User $user
     * @param string $title
     * @param string $message
     * @param array|null $data
     * @return static
     */
    public static function createBoardingNotification(User $user, string $title, string $message, ?array $data = null)
    {
        return static::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => 'BOARDING',
            'is_read' => false,
            'data' => $data ? json_encode($data) : null,
        ]);
    }
}
